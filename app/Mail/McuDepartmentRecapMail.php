<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Department;
use App\Exports\DepartmentMcuRecapExport;
use Maatwebsite\Excel\Facades\Excel;

class McuDepartmentRecapMail extends Mailable
{
    use Queueable, SerializesModels;

    public $department;
    public $records;
    public $monthName;

    /**
     * Create a new message instance.
     */
    public function __construct(Department $department, array $records, string $monthName)
    {
        $this->department = $department;
        $this->records = $records;
        $this->monthName = $monthName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rekap Jadwal MCU Departemen ' . $this->department->department_name . ' - ' . $this->monthName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mcu_department_recap',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $fileName = 'Rekap_MCU_Dept_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $this->department->department_name) . '_' . $this->monthName . '.xlsx';
        
        $export = new DepartmentMcuRecapExport($this->records);
        $excelRaw = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        return [
            Attachment::fromData(fn () => $excelRaw, $fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
