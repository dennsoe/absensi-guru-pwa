@props(['user', 'size' => 'md', 'class' => ''])

@php
    $sizes = [
        'xs' => 'width: 24px; height: 24px;',
        'sm' => 'width: 32px; height: 32px;',
        'md' => 'width: 40px; height: 40px;',
        'lg' => 'width: 56px; height: 56px;',
        'xl' => 'width: 80px; height: 80px;',
        '2xl' => 'width: 120px; height: 120px;',
    ];
    $sizeStyle = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="user-avatar {{ $class }}"
    style="{{ $sizeStyle }} position: relative; overflow: hidden; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
    <img src="{{ $user->foto_url }}" alt="{{ $user->nama }}"
        style="width: 100%; height: 100%; object-fit: cover; display: block;"
        onerror="this.src='{{ asset('assets/images/avatars/default-avatar.svg') }}'">
</div>
