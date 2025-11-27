docs/
├── 00-INDEX.md                     <-- The "Brain" (New Master Map)
├── architecture/                   <-- System design
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   └── ...
├── orchestration/                  <-- All orchestration guides
│   ├── README.md                   (was ORCHESTRATION_README.md)
│   ├── QUICKSTART.md               (was ORCHESTRATION_QUICKSTART.md)
│   ├── V2_COMPLETE.md              (was ORCHESTRATION_V2_COMPLETE.md)
│   └── agents/
├── prompts/                        <-- specialized prompts
│   ├── SUPER_DEVELOPER.md          (was SUPER_DEVELOPER_MULTISCHOOL_PROMPT.md)
│   ├── DB_REFACTOR.md              (was COMPLETE_DATABASE_REFACTOR_PROMPT.md)
│   └── COPILOT_INSTRUCTIONS.md     (copy of .github/copilot-instructions.md)
├── reports/                        <-- Historical logs & reports
│   ├── archive/                    (Move old SESSION_*.md, QUALITY_*.md here)
│   └── LATEST_STATUS.md            (New file: Current system state)
├── guides/                         <-- Developer guides
│   ├── DEVELOPER_GUIDE.md
│   ├── TESTING.md                  (was TESTING.md)
│   └── DOCKER.md                   (was DOCKER.md)
└── specs/                          <-- Feature specifications (New folder)<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container py-4">
    <h1>Attendance</h1>
    <div class="alert alert-info">Student attendance placeholder. Show records and summaries.</div>
</div>
<?= $this->endSection() ?>