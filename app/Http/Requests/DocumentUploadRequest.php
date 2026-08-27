<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Document::class);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:51200|mimes:pdf,jpg,jpeg,png,tiff,tif',
            'document_type_id' => 'required|exists:document_types,id',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A document file is required.',
            'file.max' => 'File size must not exceed 50MB.',
            'file.mimes' => 'Supported formats: PDF, JPG, PNG, TIFF.',
            'document_type_id.required' => 'Document type is required.',
            'document_type_id.exists' => 'Selected document type does not exist.',
        ];
    }
}