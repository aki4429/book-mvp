import './bootstrap';

import Alpine from 'alpinejs';

// Livewireを先に初期化
import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Alpine.jsをLivewireに登録
window.Alpine = Alpine;

// Livewireを開始
Livewire.start();

// Alpine.jsを開始
Alpine.start();
