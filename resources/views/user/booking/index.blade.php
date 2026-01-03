@extends('layouts.dashboard')

@section('title', 'Booking Saya')
@section('page-title', 'Booking Saya')

@section('sidebar')
    @include('user.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Booking</h5>
        <a href="{{ route('user.psikolog.index') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Booking Baru
        </a>
    </div>
    <div class="card-body">
        @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Psikolog</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td><code>{{ $booking->booking_code }}</code></td>
                            <td>{{ $booking->schedule->psikolog->user->name }}</td>
                            <td>{{ $booking->schedule->date->format('d M Y') }}</td>
                            <td>{{ $booking->schedule->formatted_time }}</td>
                            <td>
                                @if($booking->schedule->type == 'online')
                                    <span class="badge bg-primary">Online</span>
                                @elseif($booking->schedule->type == 'offline')
                                    <span class="badge bg-success">Offline</span>
                                @else
                                    <span class="badge bg-info">Chat</span>
                                @endif
                            </td>
                            <td>{!! $booking->status_badge !!}</td>
                            <td>
                                @if($booking->payment)
                                    {!! $booking->payment->status_badge !!}
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('user.booking.show', $booking) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$booking->isPaid() && in_array($booking->status, ['pending', 'confirmed']))
                                        <a href="{{ route('user.booking.payment', $booking) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    @endif
                                    @if($booking->status === 'confirmed' && $booking->isPaid() && $booking->consultation)
                                        @if($booking->schedule->type == 'chat')
                                        <a href="{{ route('user.consultation.chat', $booking->consultation) }}" class="btn btn-sm btn-success" title="Buka Chat">
                                            <i class="fas fa-comments"></i>
                                        </a>
                                        @elseif($booking->schedule->type == 'online')
                                        <a href="{{ route('user.consultation.chat', $booking->consultation) }}" class="btn btn-sm btn-info" title="Lihat Link Meeting">
                                            <i class="fas fa-video"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('user.consultation.show', $booking->consultation) }}" class="btn btn-sm btn-primary" title="Lihat Konsultasi">
                                            <i class="fas fa-user-md"></i>
                                        </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <span class="text-muted">
                    Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} results
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        @if ($bookings->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">&laquo; Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $bookings->previousPageUrl() }}">&laquo; Previous</a>
                            </li>
                        @endif

                        @if ($bookings->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $bookings->nextPageUrl() }}">Next &raquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next &raquo;</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h5>Belum ada booking</h5>
                <p class="text-muted">Mulai booking konsultasi dengan psikolog pilihan Anda</p>
                <a href="{{ route('user.psikolog.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Cari Psikolog
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
