@extends('layouts.admin')

@section('title', 'モーダルテスト')

@section('body')
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">モーダルテスト</h1>

    <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded">
      モーダルを開く
    </button>

    <!-- テストモーダル -->
    <div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
      <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">テストモーダル</h3>
          <p>このモーダルが表示されれば、基本的なJavaScriptが動作しています。</p>
          <div class="mt-4">
            <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded">
              閉じる
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Livewire テスト -->
    <div class="mt-8">
      <h2 class="text-xl font-bold mb-4">Livewire テスト</h2>
      @livewire('time-slot-manager')
    </div>
  </div>

  <script>
    function openModal() {
      console.log('openModal called');
      document.getElementById('testModal').classList.remove('hidden');
    }

    function closeModal() {
      console.log('closeModal called');
      document.getElementById('testModal').classList.add('hidden');
    }
  </script>

@endsection
