<?php

namespace App\Modules\Threads\Controllers\Web;

use App\Controllers\BaseController;
use App\Modules\Threads\Services\ThreadUiService;

class ThreadsController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new ThreadUiService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['messages'] = $this->service->getAll($schoolId);
        return view('App\Modules\Threads\Views\index', $data);
    }

    public function create()
    {
        return view('App\Modules\Threads\Views\create');
    }

    public function store()
    {
        $schoolId = session()->get('school_id') ?? 1;
        
        if (!$this->validate([
            'recipient_id' => 'required|integer',
            'subject' => 'required|min_length[3]',
            'body' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id' => $schoolId,
            'sender_id' => session()->get('user_id') ?? 1,
            'recipient_id' => $this->request->getPost('recipient_id'),
            'subject' => $this->request->getPost('subject'),
            'body' => $this->request->getPost('body'),
            'is_read' => 0,
        ];

        $this->service->create($data);

        return redirect()->to('/threads')->with('message', 'Message sent successfully');
    }

    public function edit($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $data['message'] = $this->service->getById($id, $schoolId);
        
        if (!$data['message']) {
            return redirect()->to('/threads')->with('error', 'Message not found');
        }

        return view('App\Modules\Threads\Views\edit', $data);
    }

    public function update($id)
    {
        $schoolId = session()->get('school_id') ?? 1;

        if (!$this->validate([
            'subject' => 'required|min_length[3]',
            'body' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'subject' => $this->request->getPost('subject'),
            'body' => $this->request->getPost('body'),
        ];

        $this->service->update($id, $data, $schoolId);

        return redirect()->to('/threads')->with('message', 'Message updated successfully');
    }

    public function delete($id)
    {
        $schoolId = session()->get('school_id') ?? 1;
        $this->service->delete($id, $schoolId);
        return redirect()->to('/threads')->with('message', 'Message deleted successfully');
    }
}
