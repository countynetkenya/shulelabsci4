<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Cfr extends Admin_Controller
{
    /** @var array<string, object> */
    protected $userCache = [];

    /** @var \App\Services\Okr\OkrCfrService */
    protected $cfrService;

    public function __construct()
    {
        parent::__construct();
        require_feature_flag('CFR_V1');

        $language = $this->session->userdata('lang');
        $this->lang->load('menu', $language);
        $this->lang->load('cfr', $language);

        $pageTitle = lang('menu_cfr');
        if ($pageTitle === 'menu_cfr') {
            $pageTitle = 'CFR';
        }

        $this->data['pageTitle'] = $pageTitle;

        $this->load->model('conversation_m');
        $this->load->model('systemadmin_m');
        $this->load->model('teacher_m');
        $this->load->model('user_m');
        $this->load->model('okr_objective_m');
        $this->load->model('okr_key_result_m');
        $this->load->model('okr_log_m');

        $this->cfrService = new \App\Services\Okr\OkrCfrService();
    }

    public function index(): void
    {
        $range = (string) $this->input->get('range');
        if (!in_array($range, ['7', '30', '90', '365', 'all'], true)) {
            $range = '30';
        }

        $schoolID = (int) $this->session->userdata('schoolID');

        $analyticsRange = $this->resolveDateRange($range);
        $cfrRecords = $this->fetchCfrRecords($schoolID, $analyticsRange['from']);
        $objectives = $this->fetchObjectives($schoolID);

        $scorecard = $this->cfrService->calculateScore($objectives, $cfrRecords);
        $summary = $this->buildSummaryMetrics($schoolID, $analyticsRange, $cfrRecords, $scorecard);

        $this->data['range'] = $range;
        $this->data['summary'] = $summary;
        $this->data['scorecard'] = $scorecard;
        $this->data['recentActivity'] = $this->fetchRecentActivity($schoolID, $analyticsRange['from']);
        $this->data['topContributors'] = $this->fetchTopContributors($schoolID, $analyticsRange['from']);
        $this->data['lastUpdatedAt'] = date(DATE_ISO8601);

        $this->data['subview'] = 'cfr/index';
        $this->load->view('_layout_main', $this->data);
    }

    protected function resolveDateRange(string $range): array
    {
        if ($range === 'all') {
            return [
                'from' => null,
            ];
        }

        $days = (int) $range;
        $from = new DateTimeImmutable(sprintf('-%d days', max($days, 1)));

        return [
            'from' => $from->format('Y-m-d 00:00:00'),
        ];
    }

    protected function fetchObjectives(int $schoolID): array
    {
        $objectives = $this->okr_objective_m->get_order_by_okr_objective([
            'schoolID' => $schoolID,
        ]);

        $records = [];
        foreach ($objectives as $objective) {
            $progress = (float) ($objective->progress_cached ?? 0.0);
            $records[] = [
                'progress' => max(0.0, min($progress / 100.0, 1.0)),
            ];
        }

        return $records;
    }

    protected function fetchCfrRecords(int $schoolID, ?string $fromDate): array
    {
        $this->db->select('conversation_msg.msg_id, conversation_msg.subject, conversation_msg.msg, conversation_msg.create_date');
        $this->db->from('conversation_msg');
        $this->db->join('conversation_message_info', 'conversation_msg.conversation_id = conversation_message_info.id');
        $this->db->where('conversation_message_info.schoolID', $schoolID);
        if ($fromDate) {
            $this->db->where('conversation_msg.create_date >=', $fromDate);
        }
        $this->db->order_by('conversation_msg.create_date', 'desc');
        $this->db->limit(500);

        $messages = $this->db->get()->result();

        $records = [];
        foreach ($messages as $message) {
            $type = $this->classifyMessageType((string) $message->subject, (string) $message->msg);
            $sentiment = $this->detectSentiment((string) $message->msg);
            $records[] = [
                'type' => $type,
                'sentiment' => $sentiment,
                'created_at' => $message->create_date,
            ];
        }

        return $records;
    }

    protected function buildSummaryMetrics(int $schoolID, array $range, array $records, array $scorecard): array
    {
        $this->db->select('COUNT(*) AS total_threads');
        $this->db->from('conversation_message_info');
        $this->db->where('schoolID', $schoolID);
        if ($range['from']) {
            $this->db->where('create_date >=', $range['from']);
        }
        $threadsRow = $this->db->get()->row();

        $this->db->select('COUNT(*) AS total_messages');
        $this->db->from('conversation_msg');
        $this->db->join('conversation_message_info', 'conversation_msg.conversation_id = conversation_message_info.id');
        $this->db->where('conversation_message_info.schoolID', $schoolID);
        if ($range['from']) {
            $this->db->where('conversation_msg.create_date >=', $range['from']);
        }
        $messagesRow = $this->db->get()->row();

        $breakdown = $scorecard['cfr_breakdown'] ?? [];

        $positive = (int) ($breakdown['positive'] ?? 0);
        $neutral = (int) ($breakdown['neutral'] ?? 0);
        $negative = (int) ($breakdown['negative'] ?? 0);
        $totalInteractions = max(1, $positive + $neutral + $negative);

        $recognition = (int) ($breakdown['recognition'] ?? 0);
        $feedback = (int) ($breakdown['feedback'] ?? 0);
        $conversation = (int) ($breakdown['conversation'] ?? 0);
        $totalCategorised = max(1, $recognition + $feedback + $conversation);

        return [
            'threads' => (int) ($threadsRow->total_threads ?? 0),
            'messages' => (int) ($messagesRow->total_messages ?? 0),
            'recognition' => $recognition,
            'feedback' => $feedback,
            'conversation' => $conversation,
            'positive' => $positive,
            'neutral' => $neutral,
            'negative' => $negative,
            'recognitionRate' => round(($recognition / $totalCategorised) * 100, 2),
            'feedbackRate' => round(($feedback / $totalCategorised) * 100, 2),
            'conversationRate' => round(($conversation / $totalCategorised) * 100, 2),
            'sentimentPositive' => round(($positive / $totalInteractions) * 100, 2),
            'sentimentNeutral' => round(($neutral / $totalInteractions) * 100, 2),
            'sentimentNegative' => round(($negative / $totalInteractions) * 100, 2),
        ];
    }

    protected function fetchRecentActivity(int $schoolID, ?string $fromDate): array
    {
        $this->db->select('conversation_msg.msg_id, conversation_msg.subject, conversation_msg.msg, conversation_msg.create_date, conversation_msg.user_id, conversation_msg.usertypeID');
        $this->db->from('conversation_msg');
        $this->db->join('conversation_message_info', 'conversation_msg.conversation_id = conversation_message_info.id');
        $this->db->where('conversation_message_info.schoolID', $schoolID);
        if ($fromDate) {
            $this->db->where('conversation_msg.create_date >=', $fromDate);
        }
        $this->db->order_by('conversation_msg.create_date', 'desc');
        $this->db->limit(15);

        $messages = $this->db->get()->result();

        $items = [];
        foreach ($messages as $message) {
            $type = $this->classifyMessageType((string) $message->subject, (string) $message->msg);
            $sentiment = $this->detectSentiment((string) $message->msg);
            $author = $this->resolveUserName((int) $message->usertypeID, (int) $message->user_id);

            $items[] = [
                'subject' => (string) $message->subject,
                'excerpt' => $this->trimExcerpt((string) $message->msg),
                'type' => $type,
                'sentiment' => $sentiment,
                'author' => $author,
                'created_at' => $message->create_date,
            ];
        }

        return $items;
    }

    protected function fetchTopContributors(int $schoolID, ?string $fromDate): array
    {
        $this->db->select('conversation_msg.user_id, conversation_msg.usertypeID, COUNT(*) as messages');
        $this->db->from('conversation_msg');
        $this->db->join('conversation_message_info', 'conversation_msg.conversation_id = conversation_message_info.id');
        $this->db->where('conversation_message_info.schoolID', $schoolID);
        if ($fromDate) {
            $this->db->where('conversation_msg.create_date >=', $fromDate);
        }
        $this->db->group_by(['conversation_msg.user_id', 'conversation_msg.usertypeID']);
        $this->db->order_by('messages', 'desc');
        $this->db->limit(8);

        $rows = $this->db->get()->result();

        $contributors = [];
        foreach ($rows as $row) {
            $contributors[] = [
                'name' => $this->resolveUserName((int) $row->usertypeID, (int) $row->user_id),
                'messages' => (int) $row->messages,
            ];
        }

        return $contributors;
    }

    protected function resolveUserName(int $usertypeID, int $userID): string
    {
        $key = $usertypeID . ':' . $userID;
        if (isset($this->userCache[$key])) {
            return $this->userCache[$key];
        }

        if ($userID <= 0) {
            return $this->userCache[$key] = lang('cfr_unknown_user');
        }

        $schoolID = (int) $this->session->userdata('schoolID');

        switch ($usertypeID) {
            case 1:
                $record = $this->systemadmin_m->get_single_systemadmin([
                    'systemadminID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
            case 2:
                $record = $this->teacher_m->get_single_teacher([
                    'teacherID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
            default:
                $record = $this->user_m->get_single_user([
                    'userID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
        }

        if ($record && isset($record->name)) {
            $this->userCache[$key] = (string) $record->name;
        } elseif ($record && isset($record->username)) {
            $this->userCache[$key] = (string) $record->username;
        } else {
            $this->userCache[$key] = lang('cfr_unknown_user');
        }

        return $this->userCache[$key];
    }

    protected function classifyMessageType(string $subject, string $body): string
    {
        $haystack = strtolower($subject . ' ' . $body);
        $recognitionTokens = ['congrats', 'congratulations', 'kudos', 'recognition', 'celebrate', 'appreciate'];
        foreach ($recognitionTokens as $token) {
            if (strpos($haystack, $token) !== false) {
                return 'recognition';
            }
        }

        $feedbackTokens = ['feedback', 'retro', 'review', 'improve', 'suggest'];
        foreach ($feedbackTokens as $token) {
            if (strpos($haystack, $token) !== false) {
                return 'feedback';
            }
        }

        return 'conversation';
    }

    protected function detectSentiment(string $body): string
    {
        $text = strtolower($body);
        $positiveWords = ['good', 'great', 'excellent', 'well done', 'thanks', 'thank you', 'amazing', 'appreciate'];
        $negativeWords = ['issue', 'problem', 'concern', 'delay', 'bad', 'poor', 'unhappy'];

        $score = 0;
        foreach ($positiveWords as $word) {
            if (strpos($text, $word) !== false) {
                $score++;
            }
        }
        foreach ($negativeWords as $word) {
            if (strpos($text, $word) !== false) {
                $score--;
            }
        }

        if ($score > 0) {
            return 'positive';
        }
        if ($score < 0) {
            return 'negative';
        }
        return 'neutral';
    }

    protected function trimExcerpt(string $text, int $length = 140): string
    {
        $clean = trim(strip_tags($text));
        if (strlen($clean) <= $length) {
            return $clean;
        }
        return substr($clean, 0, $length - 1) . '…';
    }
}
