@include('admin.chat.chat', [
    'messages' => $messages ?? collect(),
    'chatContext' => 'karyawan',
])
