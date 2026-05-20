@php
    $currentUserId = Auth::id();
@endphp

<div class="space-y-4">
    @forelse($messages as $message)
        @php
            $isMine = $message->user_id === $currentUserId;
            $bubbleClass = $isMine ? 'rounded-bl-xl rounded-tl-xl rounded-tr-xl bg-sky-600 text-white self-end' : 'rounded-br-xl rounded-tr-xl rounded-tl-xl bg-slate-100 text-slate-900 self-start';
            $senderName = $message->user->name ?? 'Unknown';
        @endphp

        <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
            <div class="mb-1 text-xs text-slate-500">{{ $senderName }} • {{ $message->created_at->format('H:i') }}</div>
            <div class="max-w-[85%] p-4 {{ $bubbleClass }}">
                @if($message->message)
                    <p class="whitespace-pre-line break-words">{{ $message->message }}</p>
                @endif

                @php
                    $fileUrl = $message->public_file_url ?? $message->file_url ?? null;
                @endphp

                @if($fileUrl)
                    <div class="mt-3">
                        @if($message->type === 'image')
                            <img src="{{ $fileUrl }}" alt="Lampiran gambar" class="max-h-72 w-full rounded-lg object-contain">
                        @elseif($message->type === 'video')
                            <video controls class="max-h-72 w-full rounded-lg">
                                <source src="{{ $fileUrl }}" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <i class="fas fa-file"></i>
                                Unduh lampiran
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-slate-600">
            Belum ada pesan. Mulai percakapan dengan tim Anda.
        </div>
    @endforelse
</div>
