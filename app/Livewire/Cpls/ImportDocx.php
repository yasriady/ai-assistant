<?php

namespace App\Livewire\Cpls;

use App\Services\Cpl\CplDocxImporter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Import CPL')]
class ImportDocx extends Component
{
    use WithFileUploads;

    public $document;

    public string $program = 'S1 Informatika';

    public bool $replace_missing = false;

    public string $resultMessage = '';

    protected function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:docx', 'max:10240'],
            'program' => ['required', 'string', 'max:100'],
            'replace_missing' => ['boolean'],
        ];
    }

    public function import(CplDocxImporter $importer): void
    {
        $this->validate();

        try {
            $result = $importer->import(
                $this->document->getRealPath(),
                $this->program,
                $this->replace_missing,
            );
        } catch (\Throwable $e) {
            $this->addError('document', $e->getMessage());

            return;
        }

        $this->resultMessage = __('ui.flash.cpl_import_finished', [
            'created' => $result['created'],
            'updated' => $result['updated'],
            'total' => $result['total'],
            'program' => $result['program'],
        ]);

        session()->flash('success', $this->resultMessage);
        $this->reset('document');
    }

    public function render()
    {
        return view('livewire.cpls.import-docx')
            ->layoutData(['header' => __('ui.cpl.import_title')]);
    }
}
