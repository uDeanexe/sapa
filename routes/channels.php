<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', fn ($user) => (bool) $user);

Broadcast::channel('chat.user.{id}', fn ($user, $id) => (int) $user->id === (int) $id);

Broadcast::channel('App.Models.User.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
