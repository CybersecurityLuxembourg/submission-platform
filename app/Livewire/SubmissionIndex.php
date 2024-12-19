<?php

namespace App\Livewire;

use App\Models\Form;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;
use Livewire\WithPagination;

class SubmissionIndex extends Component
{
    use WithPagination;

    public Form $form;
    public string $statusFilter = 'all';
    public string $search = '';
    public string $sortField = 'last_activity';
    public string $sortDirection = 'desc';
    public bool $showCompleted = false;

    protected array $queryString = [
        'statusFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'sortField' => ['except' => 'last_activity'],
        'sortDirection' => ['except' => 'desc'],
        'showCompleted' => ['except' => false]
    ];

    public function mount(Form $form): void
    {
        $this->form = $form;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getSubmissionStatusClass($status): string
    {
        return match ($status) {
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'ongoing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'submitted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'under_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'completed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }

    public function render(): Factory|View|Application
    {
        $submissions = $this->form->submissions()
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when(!$this->showCompleted, function ($query) {
                $query->whereNotIn('status', ['completed']);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->with(['user', 'form']) // Eager load relationships
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.submission-index', [
            'submissions' => $submissions,
        ]);
    }
}
