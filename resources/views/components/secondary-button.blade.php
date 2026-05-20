<button {{ $attributes->merge(['type' => 'button', 'class' => 'app-button-secondary disabled:opacity-50']) }}>
    {{ $slot }}
</button>
