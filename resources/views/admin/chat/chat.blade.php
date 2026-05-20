<x-app-layout>
    @php
        $chatContext = $chatContext ?? 'admin';
    @endphp

    @php
        $mentionUsers = \App\Models\User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn ($u) => ['id' => (int) $u->id, 'name' => (string) $u->name])
            ->values();
    @endphp
    <!-- Toast Container -->
    <div id="chat-toast-wrap" class="chat-toast-wrap" role="region" aria-live="polite" aria-label="Pemberitahuan"></div>

    <!-- Main Chat Container -->
    <div class="h-full overflow-hidden flex flex-col bg-gradient-to-br from-gray-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
        <div class="flex-1 min-h-0 flex flex-col overflow-hidden p-2 sm:p-4 lg:p-6">
            <div class="chat-panel w-full bg-white shadow-xl shadow-slate-200/50 h-full min-h-0 flex flex-col border border-slate-100 rounded-3xl overflow-hidden relative dark:bg-slate-900/70 dark:border-slate-800 dark:shadow-black/40">

                <!-- Header -->
                <header class="border-b border-slate-100 bg-white/95 backdrop-blur-md sticky top-0 z-20 dark:border-slate-800 dark:bg-slate-900/85">
                    <div class="flex w-full items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="relative h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                                <i class="fas fa-comments text-xl"></i>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div>
                                <h1 class="font-bold text-lg text-slate-800 tracking-tight dark:text-slate-100">Grup Chat</h1>
                                <p class="text-xs text-slate-500 font-medium dark:text-slate-300">Tekan kanan / tekan tahan pesan untuk opsi • Tahan tombol untuk rekam</p>
                            </div>
                        </div>
                        <div class="hidden sm:flex flex-nowrap items-center gap-3">
                            <div class="relative w-64 flex items-center">
                                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input
                                    id="chat-search"
                                    type="text"
                                    class="w-full rounded-full border-slate-200 bg-slate-50 py-2 pl-9 pr-10 text-xs font-semibold text-slate-700 placeholder:text-slate-400 focus:bg-white focus:border-indigo-400 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:bg-slate-950/80 dark:focus:border-indigo-400 dark:focus:ring-indigo-500/20"
                                    placeholder="Cari kata kunci..."
                                    autocomplete="off"
                                />
                                <button id="chat-search-clear" type="button" class="hidden inline-flex absolute right-2 top-1/2 -translate-y-1/2 h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            <span id="chat-search-count" class="hidden text-[11px] font-bold text-slate-500 dark:text-slate-300"></span>
                            <button id="internal-group-btn" type="button" class="hidden lg:inline-flex items-center gap-2 text-slate-600 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-100 hover:bg-slate-100 transition dark:text-slate-200 dark:bg-slate-950/50 dark:border-slate-800 dark:hover:bg-white/10">
                                <i class="fas fa-users text-xs text-indigo-500"></i>
                                <span class="text-xs font-semibold">Grup Internal</span>
                            </button>
                        </div>
                    </div>
                </header>

                @php 
                    $pinnedMessages = $messages->where('is_pinned', true)->values();
                    $latestPinned = $pinnedMessages->last();
                @endphp

                @if($latestPinned && $chatContext === 'admin')
                    <!-- Pinned Messages Bar -->
                    <div id="pinned-bar" class="bg-white/95 backdrop-blur-sm border-b border-slate-100 px-3 sm:px-6 py-3 flex items-center justify-between sticky top-[80px] z-10 shadow-sm group transition-all dark:bg-slate-900/85 dark:border-slate-800">
                        <button onclick="cyclePinnedMessages()" class="flex items-center gap-4 overflow-hidden flex-1 text-left hover:opacity-80 transition-opacity">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                                <i class="fas fa-thumbtack text-sm"></i>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider mb-0.5">
                                    Pesan Tersemat ({{ $pinnedMessages->count() }})
                                </p>
                                <p id="pinned-text-preview" class="text-sm text-slate-600 font-medium truncate dark:text-slate-200">
                                    {{ \Illuminate\Support\Str::limit($latestPinned->message ?? '[' . ucfirst($latestPinned->type) . ']', 50) }}
                                </p>
                            </div>
                        </button>
                        
                        <form id="unpin-form" action="{{ route('web.chats.unpin', $latestPinned->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <button type="submit" title="Lepas pin" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 flex items-center justify-center rounded-full transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                @endif
                
                <!-- Chat Messages Container -->
                <div id="chat-container" class="chat-message-list flex-1 overflow-y-auto bg-slate-50/50 dark:bg-slate-950/30" role="log">
                    <div class="w-full px-3 sm:px-6 py-5 sm:py-6 pb-6 space-y-5 sm:space-y-6">

                    
                    @foreach($messages as $msg)
                        @php $isMe = (int)$msg->user_id === (int)Auth::id(); @endphp

                        <div id="msg-{{ $msg->id }}" class="chat-message-row group flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}"
                             x-data="{ openMenu: false, menuX: 0, menuY: 0, pressTimer: null }">
                            
                            <!-- Message Metadata -->
                            <div class="flex items-center gap-2 mb-1.5 px-2 opacity-100 transition-opacity duration-200">
                                @if($msg->is_pinned)
                                    <span title="Pesan tersemat" class="inline-flex items-center gap-1 text-amber-500 text-[11px] font-bold uppercase tracking-wider">
                                        <i class="fas fa-thumbtack text-xs"></i> Tersemat
                                    </span>
                                @endif
                                @if(!$isMe)
                                    <span class="chat-sender-name text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ $msg->user->name ?? 'User' }}</span>
                                @endif
                            </div>

                            <!-- Message Bubble -->
                            <div class="relative flex items-end gap-3 w-full {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                                <!-- Context Menu -->
                                <div x-show="openMenu" x-cloak @click.away="openMenu = false" @keydown.escape.window="openMenu = false"
                                     class="message-menu fixed z-50 bg-white rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100 min-w-[160px] dark:bg-slate-900 dark:border-slate-700 dark:shadow-black/40"
                                     x-effect="$el.style.left = `${Math.min(menuX, window.innerWidth - 220)}px`; $el.style.top = `${Math.min(menuY, window.innerHeight - 180)}px`">

                                    <!-- Reply -->
                                    <button @click="startReplyFromBubble($root.querySelector('.message-bubble')); openMenu = false"
                                            class="w-full px-4 py-3 hover:bg-slate-50 text-indigo-600 text-sm font-semibold flex items-center gap-3 transition-colors dark:hover:bg-white/5">
                                        <i class="fas fa-reply w-4 text-center"></i> Balas
                                    </button>
                                    
                                    @if($chatContext === 'admin')
                                        <!-- Pin/Unpin -->
                                        <form action="{{ $msg->is_pinned ? route('web.chats.unpin', $msg->id) : route('web.chats.pin', $msg->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-3 hover:bg-slate-50 text-slate-700 text-sm font-semibold flex items-center gap-3 border-t border-slate-100 transition-colors dark:text-slate-200 dark:border-slate-800 dark:hover:bg-white/5">
                                                <i class="fas fa-thumbtack {{ $msg->is_pinned ? 'text-slate-400' : 'text-amber-500' }} w-4 text-center"></i>
                                                {{ $msg->is_pinned ? 'Lepas Pin' : 'Sematkan' }}
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit (Sender only) -->
                                    @if($isMe)
                                        <button data-message="{{ $msg->message }}"
                                                @click="editMsg({{ $msg->id }}, $el.dataset.message); openMenu = false" 
                                                class="w-full px-4 py-3 hover:bg-slate-50 text-blue-600 text-sm font-semibold flex items-center gap-3 border-t border-slate-100 transition-colors dark:border-slate-800 dark:hover:bg-white/5">
                                            <i class="fas fa-edit w-4 text-center"></i> Edit
                                        </button>

                                        <!-- Delete (Sender only) -->
                                        <form action="{{ route('web.chats.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Hapus pesan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full px-4 py-3 hover:bg-red-50 text-red-600 text-sm font-semibold flex items-center gap-3 border-t border-slate-100 transition-colors dark:border-slate-800 dark:hover:bg-rose-500/10">
                                                <i class="fas fa-trash w-4 text-center"></i> Hapus
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Message Info -->
                                    <button @click="showSeenBy({{ $msg->id }}); openMenu = false" class="w-full px-4 py-3 hover:bg-slate-50 text-slate-700 text-sm font-semibold flex items-center gap-3 border-t border-slate-100 transition-colors dark:text-slate-200 dark:border-slate-800 dark:hover:bg-white/5">
                                        <i class="fas fa-info-circle text-slate-400 w-4 text-center"></i> Info Pesan
                                    </button>
                                </div>

                                <!-- Message Bubble Content -->
                                @php
                                    $replyPreviewForDataset = $msg->message !== null && trim((string) $msg->message) !== ''
                                        ? \Illuminate\Support\Str::limit((string) $msg->message, 180)
                                        : match ((string) $msg->type) {
                                            'image' => 'Mengirim foto',
                                            'video' => 'Mengirim video',
                                            'audio', 'voice' => 'Mengirim audio',
                                            'file' => $msg->file_path ? basename((string) $msg->file_path) : 'Mengirim file',
                                            default => 'Pesan',
                                        };
                                    $replySenderForDataset = $msg->user?->name ?? 'Unknown';
                                @endphp
                                <div @contextmenu.prevent.stop="menuX = $event.clientX; menuY = $event.clientY; openMenu = true"
                                     @touchstart.passive="
                                        menuX = $event.touches[0].clientX;
                                        menuY = $event.touches[0].clientY;
                                        clearTimeout(pressTimer);
                                        pressTimer = setTimeout(() => { openMenu = true }, 450);
                                     "
                                     @touchend.passive="clearTimeout(pressTimer)"
                                     @touchmove.passive="clearTimeout(pressTimer)"
                                      class="message-bubble relative group/bubble {{ $isMe ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-sm shadow-md shadow-indigo-200/50' : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-sm shadow-sm dark:bg-slate-900/70 dark:border-slate-700 dark:text-slate-100' }} px-4 py-3 max-w-[92%] sm:max-w-xl lg:max-w-3xl 2xl:max-w-4xl cursor-context-menu hover:shadow-lg transition-all duration-200"
                                      data-msg-id="{{ $msg->id }}"
                                      data-reply-id="{{ $msg->id }}"
                                      data-reply-sender="{{ $replySenderForDataset }}"
                                      data-reply-preview="{{ $replyPreviewForDataset }}"
                                      data-reply-type="{{ $msg->type }}"
                                      title="Klik kanan untuk menu">

                                    @php
                                        $isPrivateMention = $msg->relationLoaded('recipients')
                                            && $msg->recipients
                                            && $msg->recipients->isNotEmpty();
                                        $privateMentionLabel = $isPrivateMention
                                            ? ('Private: '.($msg->recipients->pluck('name')->implode(', ') ?: ''))
                                            : '';
                                    @endphp

                                    @if($isPrivateMention)
                                        <div class="mb-2 inline-flex items-center gap-2 rounded-full px-2 py-1 text-[11px] font-black {{ $isMe ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-200' }}"
                                             title="{{ $privateMentionLabel }}">
                                            <i class="fas fa-lock text-[10px] opacity-90"></i>
                                            <span>Private</span>
                                        </div>
                                    @endif

                                    @if($msg->parent)
                                        @php
                                            $parent = $msg->parent;
                                            $parentSender = $parent->user?->name ?? 'Unknown';
                                            $parentPreview = $parent->message !== null && trim((string) $parent->message) !== ''
                                                ? \Illuminate\Support\Str::limit((string) $parent->message, 140)
                                                : match ((string) $parent->type) {
                                                    'image' => 'Mengirim foto',
                                                    'video' => 'Mengirim video',
                                                    'audio', 'voice' => 'Mengirim audio',
                                                    'file' => $parent->file_path ? basename((string) $parent->file_path) : 'Mengirim file',
                                                    default => 'Pesan',
                                                };
                                        @endphp
                                        <button type="button"
                                                onclick="scrollToMessage({{ $parent->id }});"
                                                class="mb-2 w-full text-left rounded-xl px-3 py-2 border-l-4 {{ $isMe ? 'border-white/70 bg-white/10 hover:bg-white/15' : 'border-indigo-500 bg-slate-50 hover:bg-slate-100 dark:bg-white/5 dark:hover:bg-white/10' }} transition-colors">
                                            <div class="text-[11px] font-black {{ $isMe ? 'text-white/90' : 'text-slate-700' }} truncate">
                                                Balas ke {{ $parentSender }}
                                            </div>
                                            <div class="text-[12px] {{ $isMe ? 'text-white/80' : 'text-slate-600' }} truncate">
                                                {{ $parentPreview }}
                                            </div>
                                        </button>
                                    @endif
                                    
                                    <!-- Text Message -->
                                    @if($msg->type == 'text')
                                        <p class="text-[15px] leading-relaxed whitespace-pre-wrap break-words js-linkify">{{ $msg->message }}</p>
                                    
                                    <!-- Image Message -->
                                    @elseif($msg->type == 'image')
                                        <x-lightbox-image 
                                            src="{{ $msg->public_file_url }}"
                                            alt="Chat image"
                                            title="{{ basename($msg->file_path) }}"
                                            class="block w-full max-w-xs rounded-xl cursor-pointer hover:opacity-95 transition-opacity"
                                        />
                                    
                                    <!-- Video Message -->
                                    @elseif($msg->type == 'video')
                                        <div class="relative group/video cursor-pointer w-full max-w-xs">
                                            <x-lightbox-video 
                                                src="{{ $msg->public_file_url }}"
                                                thumb="{{ $msg->poster_path ? asset($msg->poster_path) : $msg->public_file_url }}"
                                                alt="Video"
                                                title="Video • {{ $msg->created_at->format('d M Y H:i') }}"
                                                videoId="video-thumbnail-{{ $msg->id }}"
                                                class="block w-full rounded-xl shadow-sm"
                                            />
                                            <!-- Play Overlay -->
                                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-900/30 group-hover/video:bg-slate-900/40 transition-all rounded-xl">
                                                <div class="pointer-events-none h-14 w-14 rounded-full bg-white/95 text-indigo-600 flex items-center justify-center shadow-xl transform group-hover/video:scale-110 transition-transform">
                                                    <i class="fas fa-play text-2xl ml-1"></i>
                                                </div>
                                            </div>
                                            <!-- Duration -->
                                            <div class="pointer-events-none absolute right-2 bottom-2 bg-slate-900/70 text-white text-xs font-mono px-2.5 py-1 rounded-full backdrop-blur-sm">
                                                <i class="fas fa-play text-xs mr-2"></i>
                                                <span class="video-duration" data-video-id="video-thumbnail-{{ $msg->id }}">00:00</span>
                                            </div>
                                        </div>
                                    
                                    <!-- Audio/Voice Message -->
                                    @elseif($msg->type == 'audio' || $msg->type == 'voice')
                                        <div x-data="audioPlayer()" x-init="init()"
                                             class="flex items-center gap-3 w-full min-w-[200px] max-w-xs {{ $isMe ? '' : 'text-slate-800' }}">
                                            <button @click="togglePlay" 
                                                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all active:scale-95 {{ $isMe ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-indigo-100 hover:bg-indigo-200 text-indigo-600' }}">
                                                <i class="fas text-lg" :class="isPlaying ? 'fa-pause' : 'fa-play pl-0.5'"></i>
                                            </button>
                                            <div class="flex-grow">
                                                <div @click="seek($event)" class="w-full h-1.5 rounded-full relative cursor-pointer group/audio" :class="{'bg-white/30': {{ $isMe ? 'true' : 'false' }}, 'bg-slate-200': {{ !$isMe ? 'true' : 'false' }} }">
                                                    <div x-ref="progressFill" class="h-full w-0 rounded-full transition-all" :class="{'bg-white': {{ $isMe ? 'true' : 'false' }}, 'bg-indigo-600': {{ !$isMe ? 'true' : 'false' }} }"></div>
                                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow-md opacity-0 group-hover/audio:opacity-100 transition-opacity" x-ref="progressHandle"></div>
                                                </div>
                                                <div class="text-[10px] mt-1.5 opacity-80 flex justify-between font-mono font-medium">
                                                    <span x-text="currentTimeText">00:00</span>
                                                    <span x-text="durationText">00:00</span>
                                                </div>
                                            </div>
                                            <audio x-ref="audio" preload="metadata" class="hidden"
                                                @loadedmetadata="onLoadedMetadata"
                                                @timeupdate="onTimeUpdate"
                                                @ended="onEnded">
                                                <source src="{{ $msg->public_file_url }}" type="audio/{{ pathinfo($msg->file_path, PATHINFO_EXTENSION) }}">
                                            </audio>
                                        </div>
                                    
                                    <!-- File Download -->
                                    @else
                                        <a href="{{ $msg->public_file_url }}" download class="flex items-center gap-3 rounded-xl transition-all active:scale-95 hover:opacity-80">
                                            <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center {{ $isMe ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600' }}">
                                                @php
                                                    $ext = strtolower(pathinfo($msg->file_path, PATHINFO_EXTENSION));
                                                    $icons = [
                                                        'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word', 'odt' => 'fa-file-word',
                                                        'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel', 'csv' => 'fa-file-csv',
                                                        'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint', 'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive',
                                                        '7z' => 'fa-file-archive', 'txt' => 'fa-file-alt', 'log' => 'fa-file-alt', 'json' => 'fa-file-code',
                                                        'xml' => 'fa-file-code', 'yml' => 'fa-file-code', 'exe' => 'fa-file-signature', 'msi' => 'fa-file-signature'
                                                    ];
                                                    $icon = $icons[$ext] ?? 'fa-file';
                                                @endphp
                                                <i class="fas {{ $icon }} text-2xl"></i>
                                            </div>
                                            <div class="text-left min-w-0">
                                                <p class="font-bold text-sm truncate">{{ basename($msg->file_path) }}</p>
                                                <p class="text-[11px] opacity-80 font-medium">Klik untuk unduh</p>
                                            </div>
                                        </a>
                                    @endif

                                    <!-- Timestamp & Status -->
                                    <div class="flex items-center justify-end gap-1.5 mt-1.5 text-[10px] {{ $isMe ? 'text-indigo-200' : 'text-slate-400' }}">
                                        <time datetime="{{ $msg->created_at->toDateTimeString() }}" class="font-medium">{{ $msg->created_at->format('H:i') }}</time>
                                        @if($isMe)
                                            <span class="chat-seen-icon" title="{{ $msg->seenBy->count() }} pengguna membaca">
                                                <i class="fas {{ $msg->seenBy->count() > 0 ? 'fa-check-double text-blue-300' : 'fa-check' }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>


                <!-- Input Section -->
                <footer class="sticky bottom-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-100 pb-[calc(env(safe-area-inset-bottom)+1rem)] dark:bg-slate-900/85 dark:border-slate-800">
                    <form id="chat-form" action="{{ route('web.chats.store') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-3 relative px-3 sm:px-6 py-4">
                        @csrf
                        <input type="hidden" name="type" id="chat-type" value="text">
                        <input type="hidden" name="parent_id" id="reply-parent-id" value="">

                        <input type="file" name="file" id="file-input" class="hidden" onchange="handleFile(this)">

                        <!-- File Preview -->
                        <div id="file-preview" class="hidden p-3 bg-slate-50 rounded-xl text-sm flex items-center gap-4 border border-slate-200 absolute bottom-full mb-4 w-full left-0 shadow-lg shadow-slate-200/50 dark:bg-slate-950/70 dark:border-slate-700 dark:shadow-black/40">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="file-name-label" class="font-bold truncate text-slate-800 dark:text-slate-100"></p>
                                <p id="file-size-label" class="text-[11px] text-slate-500 font-medium mt-0.5 dark:text-slate-300"></p>
                            </div>
                            <button type="button" onclick="cancelFile()" title="Hapus file" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full transition-colors flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Reply Preview -->
                        <div id="reply-preview" class="hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/50 p-3 dark:border-slate-700 dark:bg-slate-950/70 dark:shadow-black/40">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-reply"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-black text-slate-700 truncate dark:text-slate-200">
                                        Balas ke <span id="reply-sender" class="text-indigo-700"></span>
                                    </div>
                                    <button type="button" id="reply-jump" class="mt-0.5 block w-full text-left text-[12px] text-slate-600 truncate hover:text-slate-800 dark:text-slate-300 dark:hover:text-slate-100">
                                        <span id="reply-text"></span>
                                    </button>
                                </div>
                                <button type="button" id="reply-cancel" title="Batal balas" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-9 h-9 rounded-full transition-colors flex-shrink-0 flex items-center justify-center">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="flex items-end gap-3">                            
                            <!-- Input Container -->
                            <div id="input-container" class="relative flex-1 flex items-end gap-2 bg-slate-50 rounded-2xl px-4 py-2 border border-slate-200 focus-within:border-indigo-400 focus-within:ring-4 focus-within:ring-indigo-50 transition-all dark:bg-slate-950/50 dark:border-slate-800 dark:focus-within:ring-indigo-500/20">
                                <!-- File Upload Button -->
                                <button type="button" id="attach-btn" class="text-slate-400 hover:text-indigo-600 transition-colors hover:bg-slate-100 p-2 rounded-full flex-shrink-0" title="Lampir file" aria-label="Lampir file">
                                    <i class="fas fa-paperclip text-lg"></i>
                                </button>

                                <!-- Attach Menu -->
                                <div id="attach-menu" class="hidden ui-pop absolute bottom-full left-2 mb-2 w-56 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 overflow-hidden origin-bottom-left dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40">
                                    <button type="button" data-attach-type="image" class="w-full px-4 py-3 text-left hover:bg-slate-50 flex items-center gap-3 dark:hover:bg-white/5">
                                        <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 inline-flex items-center justify-center">
                                            <i class="fas fa-image"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">Gambar</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-300">JPG, PNG, WEBP</div>
                                        </div>
                                    </button>
                                    <button type="button" data-attach-type="video" class="w-full px-4 py-3 text-left hover:bg-slate-50 flex items-center gap-3 border-t border-slate-100 dark:border-slate-800 dark:hover:bg-white/5">
                                        <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 inline-flex items-center justify-center">
                                            <i class="fas fa-video"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">Video</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-300">MP4, MOV, WEBM</div>
                                        </div>
                                    </button>
                                    <button type="button" data-attach-type="document" class="w-full px-4 py-3 text-left hover:bg-slate-50 flex items-center gap-3 border-t border-slate-100 dark:border-slate-800 dark:hover:bg-white/5">
                                        <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 inline-flex items-center justify-center">
                                            <i class="fas fa-file-alt"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">Dokumen</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-300">PDF, DOCX, XLSX, ZIP</div>
                                        </div>
                                    </button>
                                </div>

                                <!-- Message Input Field -->
                                <textarea name="message" id="message-input" required rows="1"
                                       spellcheck="false" autocomplete="off" autocapitalize="off"
                                       class="flex-1 bg-transparent border-0 focus:outline-none focus:ring-0 text-slate-800 placeholder-slate-400 text-[15px] resize-none max-h-32 py-2 dark:text-slate-100 dark:placeholder:text-slate-400"
                                       placeholder="Ketik pesan..."></textarea>
                                
                                <!-- Emoji Button -->
                                <button type="button" id="emoji-btn" title="Emoji" class="text-slate-400 hover:text-amber-500 transition-colors hover:bg-slate-100 p-2 rounded-full flex-shrink-0" aria-label="Emoji">
                                    <i class="fas fa-smile text-lg"></i>
                                </button>

                                <!-- Emoji Menu -->
                                <div id="emoji-menu" class="hidden ui-pop absolute bottom-full right-2 mb-2 w-[296px] max-w-[80vw] rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 overflow-hidden origin-bottom-right z-[9999] dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40">
                                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-950/50">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-face-smile text-amber-500"></i>
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">Emoji</span>
                                            <span class="ml-auto text-[11px] text-slate-500 dark:text-slate-300">Klik untuk sisipkan</span>
                                        </div>
                                    </div>
                                    <div class="p-3 max-h-72 overflow-y-auto">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-1 mb-2 dark:text-slate-400">Sering dipakai</div>
                                        <div id="emoji-recent" class="grid grid-cols-8 sm:grid-cols-10 gap-1.5 mb-3"></div>

                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-1 mb-2 dark:text-slate-400">Umum</div>
                                        <div id="emoji-grid" class="grid grid-cols-8 sm:grid-cols-10 gap-1.5"></div>
                                    </div>
                                </div>

                                <!-- Mention Menu -->
                                <div id="mention-menu" class="hidden ui-pop-sm absolute bottom-full left-2 mb-2 w-64 max-w-[80vw] rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 overflow-hidden origin-bottom-left z-[9999] dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/40">
                                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-950/50">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-at text-indigo-500"></i>
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">Tag user</span>
                                            <span class="ml-auto text-[11px] text-slate-500 dark:text-slate-300">Enter untuk pilih</span>
                                        </div>
                                    </div>
                                    <div id="mention-list" class="max-h-56 overflow-y-auto py-1"></div>
                                </div>
                            </div>

                            <!-- Recording Container (Hidden) -->
                            <div id="recording-container" class="hidden flex-1 items-center gap-4 bg-red-50/80 rounded-2xl border border-red-100 px-5 py-3 h-[52px]">
                                <i class="fas fa-circle text-red-500 animate-pulse text-xs"></i>
                                <span id="recording-timer" class="text-sm font-mono text-slate-700 font-bold tracking-wider">00:00</span>
                                <span class="text-[11px] text-slate-500 font-medium ml-auto flex items-center">
                                    <i class="fas fa-arrow-left mr-1"></i>Lepas tombol untuk kirim
                                </span>
                            </div>

                            <!-- Send/Record Button -->
                            <button type="submit" id="action-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl w-14 h-[52px] flex items-center justify-center shadow-md shadow-indigo-200 hover:shadow-lg hover:shadow-indigo-300 transition-all active:scale-95 flex-shrink-0" aria-label="Kirim atau rekam">
                                <i class="fas fa-microphone"></i>
                            </button>
                        </div>
                    </form>
                </footer>
            </div>
        </div>
    </div>


    <script>
    // --- CUSTOM STYLES & ANIMATIONS ---
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes popIn { from { opacity: 0; transform: translateY(10px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes popInSmall { from { opacity: 0; transform: translateY(6px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); } }
        
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .animate-slide-up { animation: slideUp 0.3s ease-out; }
        .highlight-msg { animation: pulse-glow 2s infinite; }
        
        .chat-message-list { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        .chat-message-list::-webkit-scrollbar { width: 6px; }
        .chat-message-list::-webkit-scrollbar-track { background: transparent; }
        .chat-message-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .chat-message-list::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .ui-fade { animation: fadeIn 160ms ease-out both; }
        .ui-pop { animation: popIn 180ms cubic-bezier(.2, .9, .2, 1) both; }
        .ui-pop-sm { animation: popInSmall 140ms cubic-bezier(.2, .9, .2, 1) both; }

        .message-menu { animation: popInSmall 140ms cubic-bezier(.2, .9, .2, 1) both; transform-origin: top left; }

        .chat-search-hidden { display: none !important; }
        .chat-search-hit .message-bubble { outline: 2px solid rgba(251, 191, 36, 0.9); outline-offset: 2px; }

        /* Hard override: some installs set a small global max-width for .message-bubble */
        .chat-panel .message-bubble { max-width: min(860px, 92vw) !important; }

        .message-bubble .mention-pill { white-space: nowrap; }
        .message-bubble.bg-indigo-600 .mention-pill { background: rgba(255,255,255,0.14); color: #fff; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18); }
        
        #edit-textarea:focus { outline: 2px solid transparent; outline-offset: 2px; }

        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in,
            .animate-slide-up,
            .highlight-msg,
            .ui-fade,
            .ui-pop,
            .ui-pop-sm,
            .message-menu {
                animation: none !important;
            }
        }
    `;
    document.head.appendChild(style);

    // --- ALPINE.JS HELPER ---
    const container = document.getElementById('chat-container');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const fileInput = document.getElementById('file-input');
    const actionBtn = document.getElementById('action-btn');
    const videoChunkSize = 512 * 1024;
    let isPreparingUpload = false;
    let bypassPrepareSubmitOnce = false;
    let pendingFile = null; // drag&drop fallback (some browsers block setting input.files)
    let autoReloadInterval = null;
    let lastMessageId = @json($messages->max('id') ?? 0);
    let chatSignature = @json($messages->map(fn($m) => $m->id.'-'.$m->updated_at?->timestamp.'-'.(int)$m->is_pinned)->implode('|'));
    let hasStartedPolling = false;
    let editingMessageId = null;
    let mediaRecorder = null;
    let audioStream = null;
    let recordedChunks = [];
    let isRecording = false;
    let recordingStartTime = null;
    let recordingTimerInterval = null;

    // Reply-to (balas)
    const replyPreviewEl = document.getElementById('reply-preview');
    const replyParentIdInput = document.getElementById('reply-parent-id');
    const replySenderEl = document.getElementById('reply-sender');
    const replyTextEl = document.getElementById('reply-text');
    const replyCancelBtn = document.getElementById('reply-cancel');
    const replyJumpBtn = document.getElementById('reply-jump');
    let replyTargetId = null;

    if (replyCancelBtn) {
        replyCancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            clearReplyPreview();
        });
    }
    if (replyJumpBtn) {
        replyJumpBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (replyTargetId) scrollToMessage(replyTargetId);
        });
    }

    const mentionUsers = @json($mentionUsers);
    const mentionMenu = document.getElementById('mention-menu');
    const mentionList = document.getElementById('mention-list');
    let mentionState = {
        open: false,
        query: '',
        startIndex: -1,
        endIndex: -1,
        activeIndex: 0,
        results: [],
    };

    function clamp(n, min, max) {
        return Math.min(Math.max(n, min), max);
    }

    function normalizeReplyPayload(payload) {
        if (!payload) return null;
        const id = Number(payload.id ?? payload.replyId ?? payload.messageId ?? payload.chatId);
        if (!Number.isFinite(id) || id <= 0) return null;
        return {
            id,
            sender: String(payload.sender ?? payload.replySender ?? payload.name ?? ''),
            preview: String(payload.preview ?? payload.replyPreview ?? payload.text ?? ''),
            type: String(payload.type ?? payload.replyType ?? 'text'),
        };
    }

    function showReplyPreview(payload) {
        const data = normalizeReplyPayload(payload);
        if (!data) return;

        replyTargetId = data.id;
        replyParentIdInput.value = String(data.id);
        replySenderEl.textContent = data.sender || 'Unknown';
        replyTextEl.textContent = data.preview || 'Pesan';
        replyPreviewEl.classList.remove('hidden');

        // Subtle focus to typing.
        try { messageInput?.focus?.(); } catch {}
    }

    function clearReplyPreview() {
        replyTargetId = null;
        replyParentIdInput.value = '';
        replySenderEl.textContent = '';
        replyTextEl.textContent = '';
        replyPreviewEl.classList.add('hidden');
    }

    function startReplyFromBubble(bubbleEl) {
        if (!bubbleEl) return;
        showReplyPreview({
            id: bubbleEl.dataset.replyId,
            sender: bubbleEl.dataset.replySender,
            preview: bubbleEl.dataset.replyPreview,
            type: bubbleEl.dataset.replyType,
        });
    }

    function getReplyParentId() {
        const value = (replyParentIdInput?.value ?? '').trim();
        const id = Number(value);
        return Number.isFinite(id) && id > 0 ? String(id) : '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function getMentionToken(value, cursorIndex) {
        const left = value.slice(0, cursorIndex);
        const atIndex = left.lastIndexOf('@');
        if (atIndex === -1) return null;
        // Stop if there is a whitespace or newline between '@' and cursor
        const token = left.slice(atIndex + 1);
        if (token.includes(' ') || token.includes('\n') || token.includes('\t')) return null;
        // '@' must be start or preceded by whitespace / common punctuation
        const prev = left[atIndex - 1] ?? ' ';
        if (prev && !/[\s\(\[\{\"'.,;:!?]/.test(prev)) return null;
        return { atIndex, query: token };
    }

    function openMentionMenu(results, query, atIndex, cursorIndex) {
        if (!mentionMenu || !mentionList) return;
        mentionState.open = true;
        mentionState.query = query;
        mentionState.startIndex = atIndex;
        mentionState.endIndex = cursorIndex;
        mentionState.activeIndex = 0;
        mentionState.results = results;
        renderMentionMenu();
        mentionMenu.classList.remove('hidden');
    }

    function closeMentionMenu() {
        if (!mentionMenu || !mentionList) return;
        mentionState.open = false;
        mentionState.query = '';
        mentionState.startIndex = -1;
        mentionState.endIndex = -1;
        mentionState.activeIndex = 0;
        mentionState.results = [];
        mentionMenu.classList.add('hidden');
        mentionList.innerHTML = '';
    }

    function renderMentionMenu() {
        if (!mentionList) return;
        const results = mentionState.results || [];
        if (results.length === 0) {
            mentionList.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Tidak ada user.</div>';
            return;
        }

        mentionList.innerHTML = results
            .map((u, idx) => {
                const active = idx === mentionState.activeIndex;
                const base = 'w-full px-4 py-2.5 text-left flex items-center gap-3 hover:bg-slate-50 transition-colors';
                const activeClass = active ? ' bg-indigo-50' : '';
                return `
                    <button type="button" class="${base}${activeClass}" data-mention-idx="${idx}">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 text-xs font-black">
                            ${escapeHtml((u.name || '?').split(' ').slice(0,2).map(w => w[0] || '').join('').toUpperCase())}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold text-slate-800">${escapeHtml(u.name)}</span>
                            <span class="block truncate text-[11px] text-slate-500">@${escapeHtml(u.name.replace(/\s+/g,'').toLowerCase())}</span>
                        </span>
                    </button>
                `;
            })
            .join('');
    }

    function filterMentionUsers(query) {
        const q = (query ?? '').trim().toLowerCase();
        const list = Array.isArray(mentionUsers) ? mentionUsers : [];
        if (!q) return list.slice(0, 8);
        return list
            .filter(u => (u.name || '').toLowerCase().includes(q))
            .slice(0, 8);
    }

    function insertMention(user) {
        if (!user || !messageInput) return;
        const value = messageInput.value;
        const start = mentionState.startIndex;
        const end = mentionState.endIndex;
        if (start < 0 || end < 0 || end < start) return;

        const before = value.slice(0, start);
        const after = value.slice(end);
        // UX: insert a readable "@handle" in the textarea.
        // The stored markup "@[id|Name]" is generated just-in-time on submit by normalizeMentionsBeforeSubmit().
        const rawName = String(user.name ?? '').trim();
        const handle = rawName
            .toLowerCase()
            .replace(/\s+/g, '')
            .replace(/[^a-z0-9_]/g, '')
            .slice(0, 40);
        const mentionText = handle.length >= 2 ? `@${handle}` : `@[${user.id}|${rawName || 'User'}]`;
        const spacer = after.length === 0 ? ' ' : (/^\s/.test(after) ? '' : ' ');
        const newValue = `${before}${mentionText}${spacer}${after}`;

        messageInput.value = newValue;
        const newCursor = (before + mentionText + spacer).length;
        messageInput.focus();
        messageInput.setSelectionRange(newCursor, newCursor);
        messageInput.dispatchEvent(new Event('input', { bubbles: true }));
        closeMentionMenu();
    }

    function handleMentionInput() {
        if (!messageInput) return;
        const cursorIndex = messageInput.selectionStart ?? 0;
        const token = getMentionToken(messageInput.value, cursorIndex);
        if (!token) {
            closeMentionMenu();
            return;
        }
        const results = filterMentionUsers(token.query);
        openMentionMenu(results, token.query, token.atIndex, cursorIndex);
    }

    function audioPlayer() {
        return {
            isPlaying: false,
            progress: 0,
            currentTimeText: '00:00',
            durationText: '00:00',
            audio: null,
            
            init() {
                this.audio = this.$refs.audio;
                this.$watch('progress', value => {
                    this.$refs.progressFill.style.width = `${value}%`;
                });
            },
            onLoadedMetadata() { this.durationText = this.formatTime(this.audio.duration); },
            onTimeUpdate() {
                if (!this.audio.duration) return;
                this.progress = (this.audio.currentTime / this.audio.duration) * 100;
                this.currentTimeText = this.formatTime(this.audio.currentTime);
            },
            onEnded() {
                this.isPlaying = false;
                this.progress = 0;
                this.audio.currentTime = 0;
            },
            togglePlay() {
                if (this.audio.paused) {
                    this.audio.play();
                    this.isPlaying = true;
                } else {
                    this.audio.pause();
                    this.isPlaying = false;
                }
            },
            seek(event) {
                const progressBar = event.currentTarget;
                const clickPosition = (event.clientX - progressBar.getBoundingClientRect().left) / progressBar.offsetWidth;
                this.audio.currentTime = clickPosition * this.audio.duration;
            },
            formatTime(seconds) {
                if (isNaN(seconds) || seconds === Infinity) return '00:00';
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
            }
        }
    }

    function onDomReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
            return;
        }
        fn();
    }

    onDomReady(() => {
        // Ensure we always land on the newest message, even when the browser restores scroll state.
        const shouldForceScrollToLatest = @json((bool)($scrollToLatest ?? true));

        function scrollChatToBottom({ behavior = 'auto', force = false } = {}) {
            if (!container) return;
            if (!force) {
                const nearBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 12;
                if (!nearBottom) return;
            }

            try {
                container.scrollTo({ top: container.scrollHeight, behavior });
            } catch (_) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function forceScrollLatest() {
            if (!shouldForceScrollToLatest) return;
            // Multiple passes to win against layout/media loading and scroll restoration.
            scrollChatToBottom({ behavior: 'auto', force: true });
            requestAnimationFrame(() => scrollChatToBottom({ behavior: 'auto', force: true }));
            setTimeout(() => scrollChatToBottom({ behavior: 'auto', force: true }), 80);
            setTimeout(() => scrollChatToBottom({ behavior: 'auto', force: true }), 260);
        }

        try {
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
        } catch (_) {}

        // Initial force on first paint.
        forceScrollLatest();

        // BFCache / reload restore hook.
        window.addEventListener('pageshow', () => forceScrollLatest());
        window.addEventListener('load', () => forceScrollLatest());

        // When media loads later, keep bottom pinned.
        container.querySelectorAll('img').forEach((img) => {
            if (img.complete) return;
            img.addEventListener('load', () => forceScrollLatest(), { once: true });
            img.addEventListener('error', () => forceScrollLatest(), { once: true });
        });
        container.querySelectorAll('video').forEach((video) => {
            video.addEventListener('loadedmetadata', () => forceScrollLatest(), { once: true });
        });
        if (messageInput.value && messageInput.value.trim() === '') {
            messageInput.value = '';
        }
        messageInput.addEventListener('input', updateActionButtonState);
        const attachBtn = document.getElementById('attach-btn');
        const attachMenu = document.getElementById('attach-menu');
        const searchInput = document.getElementById('chat-search');
        const searchClear = document.getElementById('chat-search-clear');
        const searchCount = document.getElementById('chat-search-count');

        let searchMatches = [];
        let searchActiveIndex = 0;
        let searchDebounce = null;

        function resetSearchUI() {
            searchMatches = [];
            searchActiveIndex = 0;
            if (searchClear) {
                searchClear.classList.add('hidden');
            }
            if (searchCount) searchCount.classList.add('hidden');
        }

        function clearSearch() {
            if (!searchInput) return;
            searchInput.value = '';
            applySearchFilter('');
            resetSearchUI();
        }

        function applySearchFilter(rawQuery) {
            const query = (rawQuery ?? '').trim().toLowerCase();
            const rows = Array.from(container?.querySelectorAll?.('.chat-message-row') ?? []);
            searchMatches = [];

            rows.forEach(row => {
                row.classList.remove('chat-search-hit');
                row.classList.remove('chat-search-hidden');
                const bubble = row.querySelector('.message-bubble');
                bubble?.classList.remove('ring-2', 'ring-amber-300', 'ring-offset-2', 'ring-offset-white');

                if (!query) {
                    return;
                }

                const sender = row.querySelector('.chat-sender-name');
                const text = `${(sender?.innerText || '')} ${(bubble?.innerText || '')}`.toLowerCase();
                const match = text.includes(query);
                if (match) {
                    searchMatches.push(row);
                } else {
                    row.classList.add('chat-search-hidden');
                }
            });

            if (!query) {
                resetSearchUI();
                return;
            }

            if (searchClear) {
                searchClear.classList.remove('hidden');
            }

            if (searchCount) {
                searchCount.textContent = `${searchMatches.length} hasil`;
                searchCount.classList.remove('hidden');
            }

            searchActiveIndex = 0;
            jumpToSearchMatch(0);
        }

        function jumpToSearchMatch(index) {
            if (searchMatches.length === 0) return;
            const idx = clamp(index, 0, searchMatches.length - 1);
            searchActiveIndex = idx;

            searchMatches.forEach(row => {
                row.classList.remove('chat-search-hit');
                const bubble = row.querySelector('.message-bubble');
                bubble?.classList.remove('ring-2', 'ring-amber-300', 'ring-offset-2', 'ring-offset-white');
            });

            const row = searchMatches[idx];
            row.classList.add('chat-search-hit');
            const bubble = row.querySelector('.message-bubble');
            bubble?.classList.add('ring-2', 'ring-amber-300', 'ring-offset-2', 'ring-offset-white');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function toggleAttachMenu(forceState = null) {
            if (!attachMenu) return;
            const shouldOpen = forceState === null ? attachMenu.classList.contains('hidden') : forceState;
            attachMenu.classList.toggle('hidden', !shouldOpen);
        }

        function setAcceptForAttachType(type) {
            if (!fileInput) return;
            if (type === 'image') fileInput.accept = 'image/*';
            else if (type === 'video') fileInput.accept = 'video/*';
            else fileInput.accept = '';
        }

        attachBtn?.addEventListener('click', () => {
            toggleAttachMenu();
        });

        attachMenu?.addEventListener('click', (e) => {
            const target = e.target?.closest?.('[data-attach-type]');
            if (!target) return;
            const type = target.getAttribute('data-attach-type');
            setAcceptForAttachType(type);
            toggleAttachMenu(false);
            fileInput?.click();
        });

        document.addEventListener('click', (e) => {
            if (!attachMenu || attachMenu.classList.contains('hidden')) return;
            const clickedInside = e.target?.closest?.('#attach-menu') || e.target?.closest?.('#attach-btn');
            if (!clickedInside) toggleAttachMenu(false);
        });

        // Emoji picker is initialized from `resources/js/app.js` (global initializer),
        // so this page stays resilient even if other inline code changes.

        // Debug helper: surface media load failures quickly (dev only - safe no-op in prod)
        document.addEventListener('error', (event) => {
            const target = event.target;
            if (!target) return;
            if (target.tagName === 'VIDEO' || target.tagName === 'IMG') {
                console.warn('Media failed to load:', target.tagName, target.currentSrc || target.src);
            }
        }, true);

        // Logika untuk Tekan & Tahan
        actionBtn.addEventListener('mousedown', handlePress);
        document.addEventListener('mouseup', handleRelease); // Listener global untuk melepas di mana saja
        actionBtn.addEventListener('touchstart', (e) => { e.preventDefault(); handlePress(e); });
        document.addEventListener('touchend', handleRelease); // Listener global

        // Logika untuk klik biasa (kirim teks/file). Tombol adalah submit,
        // jadi kalau tidak ada text/file, cegah submit (dipakai untuk mode rekam).
        actionBtn.addEventListener('click', (e) => {
            if (isPreparingUpload || actionBtn.disabled) {
                e.preventDefault();
                return;
            }

            const hasText = messageInput.value.trim() !== '';
            const hasFile = fileInput.files && fileInput.files.length > 0;
            if (!hasText && !hasFile) {
                e.preventDefault();
            }
        });

        chatForm.addEventListener('submit', prepareChatSubmit);
        linkifyAll();
        ensureBrowserNotificationPermission();
        startRealtimeChat();
        updateActionButtonState();

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        messageInput.addEventListener('keydown', function(e) {
            if (mentionState.open) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    mentionState.activeIndex = clamp(mentionState.activeIndex + 1, 0, (mentionState.results.length - 1));
                    renderMentionMenu();
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    mentionState.activeIndex = clamp(mentionState.activeIndex - 1, 0, (mentionState.results.length - 1));
                    renderMentionMenu();
                    return;
                }
                if (e.key === 'Enter' && e.shiftKey === false) {
                    const chosen = mentionState.results[mentionState.activeIndex];
                    if (chosen) {
                        e.preventDefault();
                        insertMention(chosen);
                        return;
                    }
                }
                if (e.key === 'Escape') {
                    closeMentionMenu();
                    return;
                }
            }
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageInput.value.trim() !== '' || (fileInput.files && fileInput.files.length > 0)) {
                    chatForm.requestSubmit();
                }
            }
        });

        // Mention (@) autocomplete
        messageInput.addEventListener('input', () => {
            handleMentionInput();
        });

        messageInput.addEventListener('click', () => {
            if (mentionState.open) handleMentionInput();
        });

        mentionList?.addEventListener('click', (e) => {
            const btn = e.target?.closest?.('[data-mention-idx]');
            if (!btn) return;
            const idx = Number(btn.getAttribute('data-mention-idx'));
            const chosen = mentionState.results?.[idx];
            if (chosen) insertMention(chosen);
        });

        document.addEventListener('click', (e) => {
            if (!mentionState.open) return;
            const inside = e.target?.closest?.('#mention-menu') || e.target?.closest?.('#message-input');
            if (!inside) closeMentionMenu();
        });

        // Search within chat
        searchInput?.addEventListener('input', () => {
            if (searchDebounce) clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => applySearchFilter(searchInput.value), 120);
        });

        searchInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (searchMatches.length === 0) return;
                const next = (searchActiveIndex + 1) % searchMatches.length;
                jumpToSearchMatch(next);
            }
            if (e.key === 'Escape') {
                clearSearch();
                searchInput.blur();
            }
        });

        searchClear?.addEventListener('click', clearSearch);

        function buildMentionIndex() {
            const idx = new Map();
            const list = Array.isArray(mentionUsers) ? mentionUsers : [];
            list.forEach((u) => {
                const name = String(u?.name ?? '').trim();
                const id = Number(u?.id ?? 0);
                if (!name || !Number.isFinite(id) || id <= 0) return;

                const aliases = new Set();
                aliases.add(name.replace(/\s+/g, '').toLowerCase());

                // Also allow matching any word token (so "@admin" matches "Kepala Admin", etc).
                const parts = name
                    .toLowerCase()
                    .replace(/[^a-z0-9_]+/g, ' ')
                    .split(' ')
                    .map(p => p.trim())
                    .filter(p => p.length >= 2);
                parts.forEach(p => aliases.add(p));

                // Two-word compact alias (e.g., "kepalaadmin")
                if (parts.length >= 2) {
                    aliases.add((parts[0] + parts[1]).slice(0, 40));
                }

                aliases.forEach((key) => {
                    if (!key) return;
                    if (!idx.has(key)) idx.set(key, { id, name });
                });
            });
            return idx;
        }

        // Auto-convert plain @nama to stored mention markup before submit.
        const mentionIndex = buildMentionIndex();

        function normalizeMentionsBeforeSubmit() {
            if (!messageInput) return;
            const value = messageInput.value || '';
            if (!value.includes('@')) return;

            // Prefix keeps punctuation/spaces so replacement doesn't change spacing.
            const re = /(^|[\s\(\[\{\"'.,;:!?])@([A-Za-z0-9_]{2,40})\b/g;
            const next = value.replace(re, (full, prefix, handle) => {
                const key = String(handle).toLowerCase();
                const hit = mentionIndex.get(key);
                if (!hit) return full;
                return `${prefix}@[${hit.id}|${hit.name}]`;
            });

            if (next !== value) messageInput.value = next;
        }

        chatForm?.addEventListener('submit', () => {
            normalizeMentionsBeforeSubmit();
        }, { capture: true });

        // Drag & drop file upload
        const dropTargets = [
            document.getElementById('chat-container'),
            document.getElementById('input-container'),
            document.body,
        ].filter(Boolean);

        let dropDepth = 0;
        const dropOverlay = (() => {
            const el = document.createElement('div');
            el.id = 'chat-drop-overlay';
            el.className = 'hidden fixed inset-0 z-[1000] flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px]';
            el.innerHTML = `
                <div class="mx-4 w-full max-w-md rounded-3xl bg-white/95 p-6 shadow-2xl ring-1 ring-slate-900/10 dark:bg-slate-900/90 dark:ring-white/10">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                            <i class="fas fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-black text-slate-900 dark:text-slate-100">Drop file untuk dikirim</div>
                            <div class="text-sm text-slate-600 dark:text-slate-300">Lepas file di mana saja di area chat.</div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(el);
            return el;
        })();

        function showDropOverlay() {
            dropOverlay?.classList?.remove?.('hidden');
        }

        function hideDropOverlay() {
            dropOverlay?.classList?.add?.('hidden');
        }

	        function applyDroppedFile(file) {
	            if (!fileInput || !file) return;
	            try {
	                pendingFile = file;

	                // Try populate the real input (may be blocked in some browsers/settings).
	                try {
	                    const dt = new DataTransfer();
	                    dt.items.add(file);
	                    fileInput.files = dt.files;
	                } catch (_) {}
	                fileInput.accept = '';

	                if (fileInput.files && fileInput.files.length > 0) {
	                    handleFile(fileInput);
	                } else {
	                    applyPendingFilePreview(file);
	                }

	                // Optional: if user drops a file, allow immediate send when there's no caption.
	                // Keeps "drop to send" feeling while still allowing cancel (they can press X quickly).
	                setTimeout(() => {
	                    const hasCaption = (messageInput?.value ?? '').trim() !== '';
	                    const hasFile = (fileInput.files && fileInput.files.length > 0) || !!pendingFile;
	                    if (!hasCaption && hasFile && !isPreparingUpload && !actionBtn?.disabled) {
	                        chatForm?.requestSubmit?.();
	                    }
	                }, 120);
	            } catch (e) {
	                console.error('Drop file failed:', e);
	            }
	        }

        // Global DnD handler (capture) so drop works anywhere and never navigates away.
        // Without this, some layouts receive the drop on an element with no listener (=> nothing happens).
        function isFileDrag(dt) {
            if (!dt) return false;
            if (dt.files && dt.files.length > 0) return true;
            if (dt.items && dt.items.length > 0) {
                return Array.from(dt.items).some((it) => it.kind === 'file');
            }
            return false;
        }

        window.addEventListener('dragenter', (e) => {
            if (!e.dataTransfer || !isFileDrag(e.dataTransfer)) return;
            e.preventDefault();
            dropDepth += 1;
            showDropOverlay();
        }, true);

        window.addEventListener('dragover', (e) => {
            // Be permissive: always prevent default when dragging over the page,
            // otherwise some browsers won't fire our drop handler reliably.
            if (!e.dataTransfer) return;
            if (!isFileDrag(e.dataTransfer)) return;
            e.preventDefault();
            try { e.dataTransfer.dropEffect = 'copy'; } catch (_) {}
        }, true);

        window.addEventListener('dragleave', () => {
            dropDepth = Math.max(0, dropDepth - 1);
            if (dropDepth === 0) hideDropOverlay();
        }, true);

        window.addEventListener('drop', (e) => {
            if (!e.dataTransfer) return;
            if (!isFileDrag(e.dataTransfer)) return;
            e.preventDefault();
            dropDepth = 0;
            hideDropOverlay();

            if (isPreparingUpload || actionBtn?.disabled) return;
            if (isRecording) cancelRecording();

            const file = e.dataTransfer.files?.[0] || e.dataTransfer.items?.[0]?.getAsFile?.();
            if (file) applyDroppedFile(file);
        }, true);

        dropTargets.forEach((t) => {
            t.addEventListener('dragenter', (e) => {
                if (!e.dataTransfer || !Array.from(e.dataTransfer.types || []).includes('Files')) return;
                dropDepth += 1;
                showDropOverlay();
            });

            t.addEventListener('dragover', (e) => {
                if (!e.dataTransfer || !Array.from(e.dataTransfer.types || []).includes('Files')) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });

            t.addEventListener('dragleave', () => {
                dropDepth = Math.max(0, dropDepth - 1);
                if (dropDepth === 0) hideDropOverlay();
            });

            t.addEventListener('drop', (e) => {
                if (!e.dataTransfer) return;
                e.preventDefault();
                dropDepth = 0;
                hideDropOverlay();

                if (isPreparingUpload || actionBtn?.disabled) return;
                if (isRecording) cancelRecording();

                const file = e.dataTransfer.files?.[0];
                if (file) applyDroppedFile(file);
            });
        });
    });

    function startRealtimeChat() {
        if (!window.Echo) {
            startAutoReload();
            return;
        }

        const currentUserId = Number(@json((int) Auth::id()));

        function onIncomingChat(payload) {
            if (!payload) return;
            if (payload.id) {
                lastMessageId = payload.id;
            }
            if (payload.sender && payload.preview) {
                showChatToast(payload.sender, payload.preview);
                showBrowserNotification(payload.sender, payload.preview);
            }
            refreshChatContainer();
        }

        // Public chat stream (non-targeted messages)
        window.Echo.channel('chat').listen('.chat.created', onIncomingChat);

        // Targeted chat stream (mention-only messages addressed to this user)
        if (Number.isFinite(currentUserId) && currentUserId > 0) {
            window.Echo.private(`chat.user.${currentUserId}`).listen('.chat.created', onIncomingChat);
        }
    }

    function linkifyAll() {
        const nodes = document.querySelectorAll('.js-linkify');
        nodes.forEach(linkifyNode);
    }

    function linkifyNode(node) {
        if (!node) return;
        const text = node.textContent || '';
        if (!text.trim()) return;

        const trimmed = text.trim();
        const singleLink = new RegExp(
            '^((https?:\\/\\/)[^\\s<]+|(www\\.)[^\\s<]+|([A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,})|(wa\\.me\\/[A-Za-z0-9._-]+))$',
            'i',
        ).test(trimmed);
        if (singleLink) {
            const raw = trimmed;
            const wrapper = document.createElement('a');
            wrapper.className = 'block rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 hover:bg-slate-100 transition';
            wrapper.rel = 'noopener noreferrer';

            let href = raw;
            let label = '';
            if (raw.includes('@') && !raw.includes('://')) {
                href = `mailto:${raw}`;
                label = 'Email';
            } else if (raw.toLowerCase().startsWith('wa.me/')) {
                href = `https://${raw}`;
                label = 'WhatsApp';
                wrapper.target = '_blank';
            } else {
                href = raw.startsWith('http://') || raw.startsWith('https://') ? raw : `https://${raw}`;
                wrapper.target = '_blank';
                try {
                    const u = new URL(href);
                    if (u.hostname === 'maps.app.goo.gl') label = 'Google Maps';
                    else if (u.hostname.includes('whatsapp.com') || u.hostname === 'wa.me') label = 'WhatsApp';
                    else label = u.hostname;
                } catch (_) {
                    label = 'Link';
                }
            }
            wrapper.href = href;

            const title = document.createElement('div');
            title.className = 'text-xs font-bold text-slate-700';
            title.textContent = label || 'Link';

            const urlLine = document.createElement('div');
            urlLine.className = 'text-xs text-indigo-700 underline decoration-dotted underline-offset-2 break-all';
            urlLine.textContent = raw;

            wrapper.appendChild(title);
            wrapper.appendChild(urlLine);

            node.textContent = '';
            node.appendChild(wrapper);
            return;
        }

	        // Mixed content: mentions + links (safe DOM)
	        const frag = document.createDocumentFragment();
	        const tokenRe = /(@\[(\d+)\|([^\]]+)\])|((https?:\/\/)[^\s<]+|(www\.)[^\s<]+|([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})|(wa\.me\/[A-Za-z0-9._-]+))/ig;
        let lastIndex = 0;
        let match;

        while ((match = tokenRe.exec(text)) !== null) {
            const start = match.index;
            const end = tokenRe.lastIndex;
            if (start > lastIndex) {
                frag.appendChild(document.createTextNode(text.slice(lastIndex, start)));
            }

            if (match[1]) {
                const userId = match[2];
                const userName = match[3];
                const pill = document.createElement('span');
                pill.className = 'mention-pill inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[12px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-100';
                pill.dataset.mentionId = userId;
                pill.textContent = `@${userName}`;
                frag.appendChild(pill);
                lastIndex = end;
                continue;
            }

            const raw = match[4];
            const a = document.createElement('a');
            a.className = 'underline decoration-dotted underline-offset-2 text-indigo-600 hover:text-indigo-800';
            a.rel = 'noopener noreferrer';
            a.target = '_blank';

            let href = raw;
	            if (/^www\./i.test(raw)) href = `https://${raw}`;
	            if (/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(raw)) href = `mailto:${raw}`;
	            if (/^wa\.me\//i.test(raw)) href = `https://${raw}`;
	            if (!/^https?:\/\//i.test(href) && !href.startsWith('mailto:')) href = `https://${href}`;

            a.href = href;
            a.textContent = raw;
            frag.appendChild(a);
            lastIndex = end;
        }

        if (lastIndex < text.length) {
            frag.appendChild(document.createTextNode(text.slice(lastIndex)));
        }

        node.textContent = '';
        node.appendChild(frag);
    }

    function handlePress(e) {
        if (isPreparingUpload || actionBtn.disabled) {
            e.preventDefault();
            return;
        }

        const hasText = messageInput.value.trim() !== '';
        const hasFile = fileInput.files && fileInput.files.length > 0;
        if (!hasText && !hasFile) {
            startRecording();
        }
    }

    function handleRelease(e) {
        if (isRecording) {
            stopRecording();
        }
    }


    // --- AUTO-RELOAD CHAT (Optimized) ---
    function startAutoReload() {
        autoReloadInterval = setInterval(function() {
            if (document.hidden) {
                return;
            }
            // Skip jika user sedang mengetik, memilih file, atau ada modal terbuka
            if (document.activeElement === messageInput || 
                messageInput.value !== '' || 
                fileInput.value !== '' ||
                isRecording ||
                document.getElementById('edit-modal') ||
                document.getElementById('image-modal') ||
                document.getElementById('video-modal')) {
                return;
            }

            fetch(`{{ route('web.chats.poll') }}?since_id=${lastMessageId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (response.status === 401 || response.status === 419) {
                        clearInterval(autoReloadInterval);
                        autoReloadInterval = null;
                        return null;
                    }
                    if (!response.ok) throw new Error('Network response error');
                    return response.json();
                })
                .then(payload => {
                    if (!payload) {
                        return;
                    }

                    if (Array.isArray(payload.new_messages) && payload.new_messages.length > 0) {
                        if (hasStartedPolling) {
                            notifyNewMessages(payload.new_messages);
                        }
                        const newest = payload.new_messages[payload.new_messages.length - 1];
                        if (newest && newest.id) {
                            lastMessageId = newest.id;
                        }
                    }

                    if (payload.signature === chatSignature) {
                        hasStartedPolling = true;
                        return;
                    }

                    chatSignature = payload.signature;
                    lastMessageId = payload.latest_id || lastMessageId;

                    // Avoid full DOM replace (which looks like "kedip") when the change is just new messages.
                    // Append-only refresh is smoother; fall back to full replace when no new messages are present
                    // (pin/edit/delete can change signature without new messages).
                    const hasNewMessages = Array.isArray(payload.new_messages) && payload.new_messages.length > 0;
                    refreshChatContainer({ mode: hasNewMessages ? 'append' : 'replace' });
                    hasStartedPolling = true;
                })
                .catch(error => console.error('Chat reload error:', error));
        }, 3000);
    }

    function isChatNearBottom(threshold = 140) {
        try {
            const el = container;
            return (el.scrollHeight - el.scrollTop - el.clientHeight) < threshold;
        } catch (_) {
            return true;
        }
    }

    function notifyNewMessages(newMessages) {
        const atBottom = isChatNearBottom();
        const hasFocus = document.hasFocus();

        newMessages
            .filter(message => !message.is_mine)
            .forEach(message => {
                // Avoid "kedip" / noisy UX when user is actively reading the latest messages.
                // Show toast only when user is away from bottom OR tab is not focused.
                if (!hasFocus || !atBottom) {
                    showChatToast(message.sender, message.preview);
                }
                showBrowserNotification(message.sender, message.preview);
            });
    }

    function showChatToast(sender, preview) {
        window.AppNotify?.showToast({
            title: sender || 'Chat baru',
            message: preview || 'Pesan baru',
            type: 'chat',
            container: '#chat-toast-wrap',
        });
    }

    function ensureBrowserNotificationPermission() {
        document.addEventListener('click', () => {
            window.AppNotify?.ensureBrowserNotificationPermission();
        }, { once: true });
    }

    function showBrowserNotification(sender, preview) {
        window.AppNotify?.showBrowserNotification({
            title: sender || 'Chat baru',
            message: preview || 'Pesan baru',
            type: 'chat',
        });
    }

    // --- INTERNAL GROUP ONLINE/OFFLINE ---
    const internalGroupBtn = document.getElementById('internal-group-btn');

    const INTERNAL_GROUP_STATUS_URL = '{{ Route::has('web.internal-group.status') ? route('web.internal-group.status') : '' }}';

    async function fetchInternalGroupStatus() {
        if (!INTERNAL_GROUP_STATUS_URL) return { online: false };

        const res = await fetch(INTERNAL_GROUP_STATUS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('Failed to load status');
        return await res.json();
    }

    function openInternalGroupModal() {
        const modalId = 'internal-group-modal';
        const existing = document.getElementById(modalId);
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'fixed inset-0 z-[1000] flex items-center justify-center p-4 ui-fade';
        modal.innerHTML = `
            <div class="absolute inset-0 bg-black/55 backdrop-blur-[2px]" data-close></div>
            <div class="relative w-full max-w-lg rounded-3xl border border-slate-200 bg-white shadow-2xl ui-pop dark:border-slate-700 dark:bg-slate-900/95">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="min-w-0">
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100">Grup Internal</div>
                        <div class="text-[12px] text-slate-500 dark:text-slate-300">Status online/offline berdasarkan aktivitas sesi.</div>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-slate-200" data-close aria-label="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="px-6 pt-4">
                    <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-950/40">
                        <button type="button" class="internal-tab flex-1 rounded-xl px-3 py-2 text-xs font-black text-slate-700 hover:bg-white transition dark:text-slate-200 dark:hover:bg-white/5" data-tab="online">
                            Online <span class="ml-2 inline-flex items-center justify-center rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] text-emerald-700 dark:text-emerald-200" data-count-online>0</span>
                        </button>
                        <button type="button" class="internal-tab flex-1 rounded-xl px-3 py-2 text-xs font-black text-slate-700 hover:bg-white transition dark:text-slate-200 dark:hover:bg-white/5" data-tab="offline">
                            Offline <span class="ml-2 inline-flex items-center justify-center rounded-full bg-slate-500/15 px-2 py-0.5 text-[11px] text-slate-700 dark:text-slate-200" data-count-offline>0</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="text-sm text-slate-600 dark:text-slate-300" data-loading>Memuat...</div>
                    <div class="hidden max-h-[60vh] overflow-y-auto pr-1" data-list></div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        let pollTimer = null;
        const close = () => {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
            modal.remove();
        };
        modal.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', close));
        modal.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

        const loadingEl = modal.querySelector('[data-loading]');
        const listEl = modal.querySelector('[data-list]');
        const onlineCountEl = modal.querySelector('[data-count-online]');
        const offlineCountEl = modal.querySelector('[data-count-offline]');
        const tabs = Array.from(modal.querySelectorAll('.internal-tab'));
        let activeTab = 'online';
        let currentUsers = [];

        const render = () => {
            const online = currentUsers.filter(u => u.is_online);
            const offline = currentUsers.filter(u => !u.is_online);
            onlineCountEl.textContent = String(online.length);
            offlineCountEl.textContent = String(offline.length);

            tabs.forEach(t => {
                const isActive = t.dataset.tab === activeTab;
                t.classList.toggle('bg-white', isActive);
                t.classList.toggle('shadow-sm', isActive);
                t.classList.toggle('dark:bg-white/10', isActive);
            });

            const list = activeTab === 'online' ? online : offline;
            listEl.innerHTML = list.map(u => {
                const dot = u.is_online
                    ? 'bg-emerald-500 shadow-emerald-500/30'
                    : 'bg-slate-400 shadow-slate-400/20';
                const role = (u.role || '').trim();
                return `
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 mb-2 last:mb-0 dark:border-slate-800 dark:bg-slate-950/30">
                        <span class="mt-0.5 h-2.5 w-2.5 rounded-full ${dot} shadow"></span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-black text-slate-900 dark:text-slate-100">${escapeHtml(u.name || 'User')}</div>
                            <div class="truncate text-[11px] text-slate-500 dark:text-slate-300">${escapeHtml(role || 'user')}</div>
                        </div>
                        <div class="text-[11px] font-black ${u.is_online ? 'text-emerald-700 dark:text-emerald-200' : 'text-slate-600 dark:text-slate-300'}">
                            ${u.is_online ? 'ON' : 'OFF'}
                        </div>
                    </div>
                `;
            }).join('') || `<div class="py-10 text-center text-sm text-slate-500 dark:text-slate-300">Tidak ada data.</div>`;
        };

        tabs.forEach(t => t.addEventListener('click', () => {
            activeTab = t.dataset.tab || 'online';
            render();
        }));

        const loadAndRender = (opts = { silent: false }) => {
            return fetchInternalGroupStatus()
                .then((data) => {
                    currentUsers = Array.isArray(data?.users) ? data.users : [];
                    if (!opts.silent) {
                        loadingEl.classList.add('hidden');
                        listEl.classList.remove('hidden');
                    }
                    render();
                })
                .catch((err) => {
                    console.error('Internal group status error:', err);
                    if (!opts.silent) {
                        loadingEl.textContent = 'Gagal memuat data.';
                    }
                });
        };

        loadAndRender({ silent: false }).then(() => {
            pollTimer = setInterval(() => {
                // stop polling automatically if modal is gone
                if (!document.getElementById(modalId)) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                    return;
                }
                loadAndRender({ silent: true });
            }, 12000);
        });
    }

    if (internalGroupBtn) {
        internalGroupBtn.addEventListener('click', openInternalGroupModal);
    }

    function refreshChatContainer(options = { mode: 'replace' }) {
        const mode = options?.mode === 'append' ? 'append' : 'replace';
        const shouldScrollToBottom = isChatNearBottom();

        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) throw new Error('Failed to refresh chat view');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nextContainer = doc.getElementById('chat-container');
                if (!nextContainer) return;

                if (mode === 'append') {
                    const currentList = container.firstElementChild;
                    const nextList = nextContainer.firstElementChild;
                    if (!currentList || !nextList) {
                        mode = 'replace';
                    } else {
                        const nextRows = Array.from(nextList.querySelectorAll('[id^="msg-"]'));
                        const toAppend = nextRows.filter((row) => {
                            const idStr = String(row.id || '').replace('msg-', '');
                            const idNum = Number.parseInt(idStr, 10);
                            return Number.isFinite(idNum) && idNum > lastMessageId;
                        });

                        if (toAppend.length === 0) {
                            return;
                        }

                        const frag = document.createDocumentFragment();
                        toAppend.forEach((row) => frag.appendChild(document.importNode(row, true)));
                        currentList.appendChild(frag);

                        // Update lastMessageId based on appended nodes.
                        const newest = toAppend[toAppend.length - 1];
                        const newestId = Number.parseInt(String(newest.id || '').replace('msg-', ''), 10);
                        if (Number.isFinite(newestId)) {
                            lastMessageId = Math.max(lastMessageId, newestId);
                        }

                        if (window.Alpine) {
                            Alpine.initTree(currentList);
                        }

                        linkifyAll();
                        generateVideoThumbnails();

                        if (shouldScrollToBottom) {
                            setTimeout(() => {
                                try {
                                    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                                } catch (_) {
                                    container.scrollTop = container.scrollHeight;
                                }
                            }, 40);
                        }

                        return;
                    }
                }

                // replace (fallback): refresh entire list
                container.innerHTML = nextContainer.innerHTML;
                if (window.Alpine) {
                    Alpine.initTree(container);
                }
                linkifyAll();
                generateVideoThumbnails();

                if (shouldScrollToBottom) {
                    setTimeout(() => {
                        try {
                            container.scrollTo({ top: container.scrollHeight, behavior: 'auto' });
                        } catch (_) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }, 60);
                }
            })
            .catch(error => console.error('View refresh error:', error));
    }

    // --- FITUR EDIT PESAN (Modal) ---
    function editMsg(id, oldMessage) {
        editingMessageId = id;

        const modal = document.createElement('div');
        modal.id = 'edit-modal';
        modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 ui-fade';
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeEditModal();
        });

        const panel = document.createElement('div');
        panel.className = 'bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 border border-slate-100 ui-pop dark:bg-slate-900 dark:border-slate-700';
        panel.addEventListener('click', (e) => e.stopPropagation());

        const header = document.createElement('div');
        header.className = 'flex items-center gap-3 mb-6';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'bg-indigo-50 rounded-xl w-10 h-10 flex items-center justify-center dark:bg-indigo-500/15';
        iconWrap.innerHTML = '<i class="fas fa-edit text-indigo-600 text-lg"></i>';

        const title = document.createElement('h3');
        title.className = 'text-xl font-bold text-slate-800 dark:text-slate-100';
        title.textContent = 'Edit Pesan';

        header.appendChild(iconWrap);
        header.appendChild(title);

        const textarea = document.createElement('textarea');
        textarea.id = 'edit-textarea';
        textarea.className = 'w-full border border-slate-200 rounded-xl p-4 mb-6 focus:outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 resize-none text-slate-700 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-100 dark:focus:ring-indigo-500/20';
        textarea.rows = 5;
        textarea.placeholder = 'Ubah pesan Anda...';
        textarea.value = String(oldMessage ?? '');

        const actions = document.createElement('div');
        actions.className = 'flex justify-end gap-3';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all active:scale-95';
        cancelBtn.textContent = 'Batal';
        cancelBtn.addEventListener('click', closeEditModal);

        const saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition-all active:scale-95 shadow-md shadow-indigo-200';
        saveBtn.textContent = 'Simpan';
        saveBtn.addEventListener('click', submitEdit);

        actions.appendChild(cancelBtn);
        actions.appendChild(saveBtn);

        panel.appendChild(header);
        panel.appendChild(textarea);
        panel.appendChild(actions);
        modal.appendChild(panel);
        document.body.appendChild(modal);

        textarea.focus();
        textarea.select();
    }

    function closeEditModal(event) {
        const modal = document.getElementById('edit-modal');
        if (modal) modal.remove();
        editingMessageId = null;
    }

    function submitEdit() {
        if (!editingMessageId) return;
        
        const newMessage = document.getElementById('edit-textarea').value.trim();
        const oldMessage = document.querySelector(`[data-msg-id="${editingMessageId}"]`)?.innerText;
        
        if (newMessage === '' || newMessage === oldMessage) {
            closeEditModal();
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/messages') }}/${editingMessageId}`;
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('_method', 'PUT');
        formData.append('message', newMessage);
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: formData
        })
        .then(res => {
            if (res.ok) {
                location.reload();
            } else {
                alert('Gagal mengubah pesan');
            }
        })
        .catch(err => {
            console.error('Edit error:', err);
            alert('Terjadi kesalahan saat mengubah pesan');
        })
        .finally(() => closeEditModal());
    }

    // Allow Enter to submit (Shift+Enter for new line)
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('edit-modal');
        if (modal && e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            submitEdit();
        }
    });

    // --- FITUR INFO PESAN (SEEN BY) ---
    function showSeenBy(id) {
        fetch(`{{ url('/messages') }}/${id}/seen`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (res.status === 401) throw new Error('Unauthorized');
            if (!res.ok) throw new Error('Failed to fetch');
            return res.json();
        })
        .then(data => {
            let content = '';
            if (data && data.length > 0) {
                const userList = data.map(u => `
                    <div class="flex items-center justify-between py-3 px-4 hover:bg-gray-50 transition-colors border-b last:border-b-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                ${u.name.charAt(0).toUpperCase()}
                            </div>
                            <span class="text-slate-800 font-semibold">${u.name}</span>
                        </div>
                        <span class="text-slate-500 text-xs font-mono">${new Date(u.seen_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' })}</span>
                    </div>
                `).join('');
                content = `<div class="divide-y">${userList}</div>`;
            } else {
                content = '<div class="text-center text-slate-500 py-8 dark:text-slate-300"><i class="fas fa-inbox text-3xl opacity-30 mb-2 block"></i>Belum ada yang membaca pesan ini</div>';
            }

            const modal = document.createElement('div');
            modal.id = 'seen-by-modal';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 ui-fade" onclick="closeSeenByModal(event)">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden ui-pop dark:bg-slate-900 dark:border dark:border-slate-700" onclick="event.stopPropagation()">
                        <div class="flex justify-between items-center p-6 border-b border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex items-center gap-3">
                                <div class="bg-indigo-50 rounded-xl w-10 h-10 flex items-center justify-center dark:bg-indigo-500/15">
                                    <i class="fas fa-info-circle text-indigo-600 text-lg"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Dilihat oleh (${data.length})</h3>
                            </div>
                            <button onclick="closeSeenByModal()" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 w-8 h-8 rounded-full flex items-center justify-center transition-all dark:hover:bg-white/10 dark:hover:text-slate-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="max-h-96 overflow-y-auto dark:text-slate-100">${content}</div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        })
        .catch(err => {
            console.error('Seen by error:', err);
            if (String(err?.message || '').toLowerCase().includes('unauthorized')) {
                alert('Sesi login habis. Silakan login ulang.');
                return;
            }
            alert("Gagal memuat info pesan.");
        });
    }

    function closeSeenByModal(event) {
        if (event && event.target.id !== 'seen-by-modal') return;
        const modal = document.getElementById('seen-by-modal');
        if (modal) modal.remove();
    }

    // --- FITUR PINNED MESSAGES ---
    const pinnedData = @json($messages->where('is_pinned', true)->values());
    let currentPinIndex = pinnedData.length - 1;

    function cyclePinnedMessages() {
        if (pinnedData.length <= 0) return;
        if (pinnedData.length === 1) {
            scrollToMessage(pinnedData[0].id);
            return;
        }
        
        currentPinIndex = (currentPinIndex - 1 + pinnedData.length) % pinnedData.length;
        const currentPin = pinnedData[currentPinIndex];
        
        const previewText = currentPin.type === 'text' ? currentPin.message : `[${currentPin.type.toUpperCase()}]`;
        const previewEl = document.getElementById('pinned-text-preview');
        if (previewEl) previewEl.innerText = previewText;
        
        const unpinForm = document.getElementById('unpin-form');
        if (unpinForm) unpinForm.action = `{{ url('/messages') }}/${currentPin.id}/unpin`;
        
        scrollToMessage(currentPin.id);
    }

    function scrollToMessage(id) {
        const el = document.getElementById(`msg-${id}`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('highlight-msg'); 
            setTimeout(() => el.classList.remove('highlight-msg'), 2000);
        }
    }

	    // --- FITUR UPLOAD FILE ---
	    function applyPendingFilePreview(file) {
	        if (!file) return;

	        document.getElementById('file-name-label').innerText = file.name || 'file';
	        document.getElementById('file-size-label').innerText = `${formatFileSize(file.size)}${isCompressibleImage(file) ? ' - akan dikompres' : String(file.type || '').startsWith('video/') ? ' - dikirim bertahap' : ''}`;
	        document.getElementById('file-preview').classList.remove('hidden');
	        document.getElementById('message-input').removeAttribute('required');

	        const typeInput = document.getElementById('chat-type');
	        const ext = String(file.name || '').split('.').pop().toLowerCase();

	        if (String(file.name || '').startsWith('voice_') && ext === 'webm') {
	            typeInput.value = 'voice';
	        } else if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) {
	            typeInput.value = 'image';
	        } else if (['mp4','mov','avi','webm','mkv','flv','m4v', '3gp'].includes(ext)) {
	            typeInput.value = 'video';
	        } else if (['mp3','wav','ogg','m4a','aac','flac'].includes(ext)) {
	            typeInput.value = 'voice';
	        } else {
	            typeInput.value = 'file';
	        }

	        updateActionButtonState();
	    }

	    function handleFile(input) {
	        if (input.files && input.files[0]) {
	            const f = input.files[0];
	            pendingFile = f;

	            document.getElementById('file-name-label').innerText = f.name;
	            document.getElementById('file-size-label').innerText = `${formatFileSize(f.size)}${isCompressibleImage(f) ? ' - akan dikompres' : f.type.startsWith('video/') ? ' - dikirim bertahap' : ''}`;
	            document.getElementById('file-preview').classList.remove('hidden');
	            document.getElementById('message-input').removeAttribute('required');
            
            const typeInput = document.getElementById('chat-type');
            const ext = f.name.split('.').pop().toLowerCase();

            if (f.name.startsWith('voice_') && ext === 'webm') {
                typeInput.value = 'voice';
            } else if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) {
                typeInput.value = 'image';
            } else if (['mp4','mov','avi','webm','mkv','flv','m4v', '3gp'].includes(ext)) {
                typeInput.value = 'video';
            } else if (['mp3','wav','ogg','m4a','aac','flac'].includes(ext)) {
                typeInput.value = 'voice';
            } else {
                typeInput.value = 'file';
            }
            updateActionButtonState();
	        }
	    }

	    async function sendFileViaFetch(file) {
	        if (!file) return false;

	        const token = document.querySelector('input[name="_token"]')?.value;
	        if (!token) {
	            alert('Token tidak ditemukan. Refresh halaman lalu coba lagi.');
	            return false;
	        }

	        const type = document.getElementById('chat-type')?.value || 'file';
	        const msg = (messageInput?.value ?? '').trim();

	        const formData = new FormData();
	        formData.append('_token', token);
	        formData.append('type', type);
	        formData.append('message', msg);
	        const parentId = getReplyParentId();
	        if (parentId) formData.append('parent_id', parentId);
	        formData.append('file', file, file.name || `upload_${Date.now()}`);

	        try {
	            const response = await fetch('{{ route('web.chats.store') }}', {
	                method: 'POST',
	                headers: {
	                    'Accept': 'application/json',
	                    'X-CSRF-TOKEN': token,
	                    'X-Requested-With': 'XMLHttpRequest',
	                },
	                body: formData,
	            });

	            if (!response.ok) {
	                const errorText = await response.text();
	                console.error('Upload error:', errorText);
	                alert('Gagal mengirim file. Coba lagi.');
	                return false;
	            }

	            location.reload();
	            return true;
	        } catch (error) {
	            console.error('Upload error:', error);
	            alert('Gagal mengirim file. Coba lagi.');
	            return false;
	        }
	    }

	    async function prepareChatSubmit(event) {
	        // Submit kedua setelah kompresi/chunk-prepare: jangan di-intercept lagi.
	        if (bypassPrepareSubmitOnce) {
            bypassPrepareSubmitOnce = false;
            return;
        }

        if (isPreparingUpload) {
            event.preventDefault();
            return;
        }

	        const inputFile = (fileInput.files && fileInput.files.length > 0) ? fileInput.files[0] : null;
	        const file = inputFile || pendingFile;
	        if (!file) return;

	        // Some browsers block programmatically assigning file inputs (DnD).
	        // If we don't have a real input file, we will upload via fetch.
	        const hasRealInputFile = !!inputFile;
	        if (document.getElementById('chat-type').value === 'video') {
	            event.preventDefault();
	            await uploadVideoInChunks(file);
	            return;
	        }

	        if (!isCompressibleImage(file)) {
	            if (!hasRealInputFile) {
	                event.preventDefault();
	                isPreparingUpload = true;
	                lockSendButton();
	                try {
	                    await sendFileViaFetch(file);
	                } finally {
	                    isPreparingUpload = false;
	                    unlockSendButton();
	                }
	            }
	            return;
	        }

        event.preventDefault();
        isPreparingUpload = true;
        const originalLabel = document.getElementById('file-size-label').innerText;
        document.getElementById('file-size-label').innerText = `${originalLabel} - memproses...`;
        lockSendButton();

	        try {
	            const compressedFile = await compressImageFile(file);
	            const chosen = compressedFile.size < file.size ? compressedFile : file;
	            if (compressedFile.size < file.size) {
	                document.getElementById('file-name-label').innerText = compressedFile.name;
	                document.getElementById('file-size-label').innerText = `${formatFileSize(file.size)} -> ${formatFileSize(compressedFile.size)}`;
	            } else {
	                document.getElementById('file-size-label').innerText = `${formatFileSize(file.size)} - original dipakai`;
	            }

	            if (hasRealInputFile) {
	                const dataTransfer = new DataTransfer();
	                dataTransfer.items.add(chosen);
	                fileInput.files = dataTransfer.files;
	                pendingFile = fileInput.files[0] || pendingFile;

	                // Penting: requestSubmit() akan memicu event submit lagi.
	                // Kalau isPreparingUpload masih true, submit kedua akan dibatalkan dan pesan tidak terkirim.
	                bypassPrepareSubmitOnce = true;
	                isPreparingUpload = false;
	                unlockSendButton();
	                chatForm.requestSubmit();
	                return;
	            }

	            pendingFile = chosen;
	            await sendFileViaFetch(chosen);
	            return;
	        } catch (error) {
	            console.error('Image compression error:', error);
	            document.getElementById('file-size-label').innerText = `${formatFileSize(file.size)} - original dipakai`;

	            if (hasRealInputFile) {
	                bypassPrepareSubmitOnce = true;
	                isPreparingUpload = false;
	                unlockSendButton();
	                chatForm.requestSubmit();
	                return;
	            }

	            await sendFileViaFetch(file);
	            return;
	        } finally {
	            isPreparingUpload = false;
	            unlockSendButton();
	        }
	    }

    async function uploadVideoInChunks(file) {
        if (isPreparingUpload) return;

        isPreparingUpload = true;
        lockSendButton();

        const uploadId = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        const totalChunks = Math.ceil(file.size / videoChunkSize);
        const token = document.querySelector('input[name="_token"]').value;
        const label = document.getElementById('file-size-label');

        try {
            for (let index = 0; index < totalChunks; index++) {
                const start = index * videoChunkSize;
                const chunk = file.slice(start, Math.min(start + videoChunkSize, file.size));
                const formData = new FormData();
                formData.append('_token', token);
                formData.append('upload_id', uploadId);
                formData.append('chunk_index', index);
                formData.append('total_chunks', totalChunks);
                formData.append('chunk', chunk, `${file.name}.part${index}`);

                const progress = Math.round((index / totalChunks) * 100);
                label.innerText = `${formatFileSize(file.size)} - upload ${progress}%`;

                const response = await fetch('{{ route('web.chats.chunks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                const uploadedProgress = Math.round(((index + 1) / totalChunks) * 100);
                label.innerText = `${formatFileSize(file.size)} - upload ${uploadedProgress}%`;
            }

            label.innerText = `${formatFileSize(file.size)} - memproses video...`;

            const completeData = new FormData();
            completeData.append('_token', token);
            completeData.append('upload_id', uploadId);
            completeData.append('total_chunks', totalChunks);
            completeData.append('type', 'video');
            completeData.append('message', messageInput.value.trim());
            const parentId = getReplyParentId();
            if (parentId) completeData.append('parent_id', parentId);
            completeData.append('file_name', file.name);

            const completeResponse = await fetch('{{ route('web.chats.chunks.complete') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: completeData,
            });

            if (!completeResponse.ok) {
                throw new Error(await completeResponse.text());
            }

            location.reload();
        } catch (error) {
            console.error('Chunk upload error:', error);
            alert('Gagal mengirim video. Coba lagi.');
        } finally {
            isPreparingUpload = false;
            unlockSendButton();
        }
    }

    function lockSendButton() {
        actionBtn.disabled = true;
        actionBtn.setAttribute('aria-disabled', 'true');
        actionBtn.classList.add('opacity-70', 'cursor-wait', 'pointer-events-none');
    }

    function unlockSendButton() {
        actionBtn.disabled = false;
        actionBtn.removeAttribute('aria-disabled');
        actionBtn.classList.remove('opacity-70', 'cursor-wait', 'pointer-events-none');
        updateActionButtonState();
    }

    function compressImageFile(file) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                const maxDimension = 1600;
                const ratio = Math.min(1, maxDimension / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * ratio));
                canvas.height = Math.max(1, Math.round(image.height * ratio));

                const context = canvas.getContext('2d');
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(blob => {
                    if (!blob) {
                        reject(new Error('Canvas compression failed'));
                        return;
                    }

                    const baseName = file.name.replace(/\.[^.]+$/, '');
                    resolve(new File([blob], `${baseName}.jpg`, {
                        type: 'image/jpeg',
                        lastModified: Date.now(),
                    }));
                }, 'image/jpeg', 0.78);
            };

            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Image could not be loaded'));
            };

            image.src = objectUrl;
        });
    }

    function isCompressibleImage(file) {
        return ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
    }

    function updateActionButtonState() {
        const hasText = messageInput.value.trim() !== '';
        const hasFile = fileInput.files && fileInput.files.length > 0;
        const inputContainer = document.getElementById('input-container');
        const recordingContainer = document.getElementById('recording-container');

        if (isRecording) {
            actionBtn.innerHTML = '<i class="fas fa-stop"></i>';
            actionBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-indigo-200', 'hover:shadow-indigo-300');
            actionBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'shadow-red-200', 'hover:shadow-red-300', 'animate-pulse');

            inputContainer.classList.add('hidden');
            recordingContainer.classList.remove('hidden');
            recordingContainer.classList.add('flex');

            fileInput.disabled = true;
            return;
        }

        // Kondisi tidak sedang merekam
        inputContainer.classList.remove('hidden');
        recordingContainer.classList.add('hidden');
        recordingContainer.classList.remove('flex');

        if (hasText || hasFile) {
            actionBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            actionBtn.setAttribute('aria-label', 'Kirim pesan');
        } else {
            actionBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            actionBtn.setAttribute('aria-label', 'Tahan untuk merekam suara');
        }

        actionBtn.classList.remove('bg-red-500', 'hover:bg-red-600', 'shadow-red-200', 'hover:shadow-red-300', 'animate-pulse');
        actionBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-indigo-200', 'hover:shadow-indigo-300');

        fileInput.disabled = false;
    }

    function updateRecordingTimer() {
        if (!recordingStartTime) return;
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        const minutes = String(Math.floor(elapsed / 60)).padStart(2, '0');
        const seconds = String(elapsed % 60).padStart(2, '0');
        document.getElementById('recording-timer').innerText = `${minutes}:${seconds}`;
    }

    async function startRecording() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser Anda tidak mendukung rekaman suara.');
            return;
        }
        // Jangan mulai merekam jika sudah ada file/teks
        if (messageInput.value.trim() !== '' || (fileInput.files && fileInput.files.length > 0)) return;

        try {
            // Request microphone access
            audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            recordedChunks = [];
            mediaRecorder = new MediaRecorder(audioStream);

            mediaRecorder.addEventListener('dataavailable', event => {
                if (event.data && event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            });

	        mediaRecorder.addEventListener('stop', () => {
	            if (!isRecording) { // Was cancelled
	                if (audioStream) {
	                    audioStream.getTracks().forEach(track => track.stop());
	                    audioStream = null;
	                }
	                return;
	            }

                const recordingDuration = Date.now() - recordingStartTime;
	            if (recordingDuration < 1000) {
	                alert('Rekaman terlalu singkat (minimal 1 detik).');
	                isRecording = false;
	                recordedChunks = [];
	                if (audioStream) {
	                    audioStream.getTracks().forEach(track => track.stop());
	                    audioStream = null;
	                }
	                updateActionButtonState();
	                return;
	            }
                const blob = new Blob(recordedChunks, { type: 'audio/webm' });
                sendVoiceMessage(blob);
            });

            mediaRecorder.start();
            recordingStartTime = Date.now();
            isRecording = true;
            recordingTimerInterval = setInterval(updateRecordingTimer, 500);
            updateActionButtonState();
        } catch (error) {
            console.error('Rekam suara error:', error);
            alert('Tidak dapat mengakses mikrofon. Periksa izin browser.');
        }
    }

    function stopRecording() {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
        }
        clearInterval(recordingTimerInterval);
        document.getElementById('recording-timer').innerText = '00:00';
        // The 'stop' event listener will handle the rest
    }

    function cancelRecording() {
        isRecording = false; // Set to false before stopping to prevent sending

        if (audioStream) {
            audioStream.getTracks().forEach(track => track.stop());
            audioStream = null;
        }
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop(); // Stop without triggering send
        }
        recordedChunks = [];
        clearInterval(recordingTimerInterval);
        document.getElementById('recording-timer').innerText = '00:00';
        updateActionButtonState();
    }

    async function sendVoiceMessage(blob) {
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('type', 'voice');
        formData.append('message', '');
        const parentId = getReplyParentId();
        if (parentId) formData.append('parent_id', parentId);
        const file = new File([blob], `voice_${Date.now()}.webm`, { type: blob.type });
        formData.append('file', file);

        try {
            const response = await fetch('{{ route('web.chats.store') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData,
            });
            if (response.ok) {
                location.reload();
            } else {
                const errorText = await response.text();
                console.error('Gagal mengirim voice chat:', errorText);
                alert('Gagal mengirim voice chat.');
            }
        } catch (error) {
            console.error('Voice upload error:', error);
            alert('Gagal mengirim voice chat.');
        }
	        finally {
	            isRecording = false;
	            recordedChunks = [];
	            if (audioStream) {
	                audioStream.getTracks().forEach(track => track.stop());
	                audioStream = null;
	            }
	            updateActionButtonState();
	        }
	    }

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

	    function cancelFile() {
	        document.getElementById('file-input').value = "";
	        document.getElementById('file-preview').classList.add('hidden');
	        document.getElementById('chat-type').value = 'text';
	        document.getElementById('message-input').setAttribute('required', 'required');
	        pendingFile = null;
	        updateActionButtonState();
	        document.getElementById('message-input').style.height = 'auto';
	    }

    // --- FITUR MODAL IMAGE & VIDEO ---
    function downloadFile(src) {
        const a = document.createElement('a');
        a.href = src;
        a.download = src.split('/').pop();
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function escapeHtml(value) {
        if (window.AppNotify?.escapeHtml) {
            return window.AppNotify.escapeHtml(value);
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll(`'`, '&#039;');
    }

    // Close modal dengan ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            try { window.AppLightbox?.close?.(); } catch {}
            try { closeSeenByModal?.(); } catch {}
            try { closeEditModal?.(); } catch {}
            try { clearReplyPreview?.(); } catch {}
        }
    });

    // --- GENERATE VIDEO THUMBNAIL ---
    function generateVideoThumbnail(videoElement, videoId) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        const handleLoadedMetadata = () => {
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;
            videoElement.currentTime = 0.1; // Ambil frame di 0.1 detik
            const durationEl = document.querySelector(`[data-video-id="${videoElement.id}"]`);
            if (durationEl && Number.isFinite(videoElement.duration)) {
                durationEl.innerText = formatDuration(videoElement.duration);
            }
        };

        const handleSeeked = () => {
            try {
                ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
                videoElement.poster = thumbnail;
            } catch (e) {
                console.error('Error generating thumbnail:', e);
            } finally {
                videoElement.removeEventListener('loadedmetadata', handleLoadedMetadata);
                videoElement.removeEventListener('seeked', handleSeeked);
            }
        };

        videoElement.addEventListener('loadedmetadata', handleLoadedMetadata, { once: true });
        videoElement.addEventListener('seeked', handleSeeked, { once: true });
    }

    // Generate thumbnail untuk semua video saat halaman load
    function generateVideoThumbnails() {
        const videoElements = document.querySelectorAll('video[id^="video-thumbnail-"]');
        videoElements.forEach(video => {
            generateVideoThumbnail(video, video.id.replace('video-thumbnail-', ''));
        });
    }

    function formatDuration(seconds) {
        const totalSeconds = Math.max(0, Math.floor(seconds || 0));
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
        const remainingSeconds = String(totalSeconds % 60).padStart(2, '0');

        return `${minutes}:${remainingSeconds}`;
    }

    document.addEventListener('DOMContentLoaded', generateVideoThumbnails);

    </script>
</x-app-layout>
