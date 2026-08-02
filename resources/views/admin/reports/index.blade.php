@extends('layouts.admin')

@section('title', 'Denúncias - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1"><i class="fa-solid fa-flag text-danger me-2"></i>Denúncias</h2>
        <p class="text-muted small mb-0">Prioridade automática conforme a gravidade informada.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-danger px-3 py-2 rounded-pill">{{ $criticalCount }} alta prioridade</span>
        <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $openCount }} em análise</span>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Prioridade</th>
                    <th>Motivo</th>
                    <th>Conteúdo</th>
                    <th>Denúncias</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th class="text-end pe-4">Ação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $priorityClass = match($report->severity) {
                            'critical' => 'danger',
                            'misleading' => 'warning',
                            default => 'success',
                        };
                    @endphp
                    <tr>
                        <td class="ps-4"><span class="badge bg-{{ $priorityClass }} bg-opacity-10 text-{{ $priorityClass }}">{{ $report->priority_label }}</span></td>
                        <td class="fw-semibold"><small class="text-muted d-block">{{ $report->reference }}</small>{{ $report->reason_label }}</td>
                        <td>
                            <div class="fw-bold text-truncate" style="max-width: 260px;">{{ $report->ad_title_snapshot }}</div>
                            <small class="text-muted">
                                {{ $report->subject_label }}
                                @if($report->subject_type === 'store')
                                    {{ $report->store_id ? '#' . $report->store_id : 'excluída' }}
                                @else
                                    {{ $report->ad_id ? '#' . $report->ad_id : 'excluído' }}
                                @endif
                            </small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $reportCounts[$report->subject_key] ?? 1 }}</span></td>
                        <td>{{ strtoupper($report->status) }}</td>
                        <td><small>{{ $report->created_at->format('d/m/Y H:i') }}</small></td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Abrir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Nenhuma denúncia registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $reports->links() }}</div>
@endsection
