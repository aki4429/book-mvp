import './bootstrap';

// Livewire v3では自動初期化されるため、Alpine.jsの設定のみ行う
import Alpine from 'alpinejs';

// Alpine.jsをグローバルに設定
window.Alpine = Alpine;

// DOMContentLoadedイベント後にAlpine.jsを開始
document.addEventListener('DOMContentLoaded', function () {
    Alpine.start();
});
