<?php

namespace App\Livewire\Concerns;

trait ConfirmsDeletion
{
    public bool $confirmingDeletion = false;

    public ?int $deletingId = null;

    public string $deleteConfirmation = '';

    public string $deletingLabel = '';

    public function askDelete(int $id, string $label = ''): void
    {
        $this->deletingId = $id;
        $this->deletingLabel = $label;
        $this->deleteConfirmation = '';
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->deletingId = null;
        $this->deleteConfirmation = '';
        $this->deletingLabel = '';
    }

    public function confirmDelete(): void
    {
        if (! $this->isDeleteConfirmed()) {
            return;
        }

        if ($this->deletingId === null) {
            return;
        }

        $id = $this->deletingId;
        $this->cancelDelete();
        $this->delete($id);
    }

    protected function isDeleteConfirmed(): bool
    {
        return strtolower(trim($this->deleteConfirmation)) === 'delete';
    }
}
