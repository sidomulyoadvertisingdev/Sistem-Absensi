@extends('layouts.app')

@section('title', 'Tambah Webhook Integrasi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-1">Tambah Webhook Integrasi</h1>
            <p class="text-muted mb-0">Sistem akan POST JSON ke URL tujuan saat event terpilih terjadi.</p>
        </div>
        <a href="{{ route('admin.integration-webhooks.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Form Webhook</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.integration-webhooks.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Webhook</label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="{{ old('name') }}" placeholder="Contoh: Mobile SMPO" required>
                </div>

                <div class="form-group">
                    <label for="webhook_url">URL Tujuan</label>
                    <input type="url" name="webhook_url" id="webhook_url" class="form-control"
                           value="{{ old('webhook_url') }}" placeholder="https://app-eksternal.com/webhook/absensi" required>
                    <small class="text-muted">Harus HTTPS (atau http untuk testing lokal).</small>
                </div>

                <div class="form-group">
                    <label class="d-block">Event yang Dikirim</label>
                    @foreach($eventOptions as $event => $label)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input"
                                   id="event_{{ $event }}" name="events[]" value="{{ $event }}"
                                   {{ in_array($event, old('events', ['attendance']), true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="event_{{ $event }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active"
                               name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan Webhook
                </button>
            </form>
        </div>
    </div>
@endsection
