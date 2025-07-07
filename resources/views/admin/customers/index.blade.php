@extends('layouts.admin')
@section('page-title', '顧客一覧')

@section('body')
    <h2 class="text-xl font-semibold mb-6">顧客一覧</h2>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">名前</th>
                    <th class="px-4 py-2 text-left">e-mail</th>
                    <th class="px-4 py-2 text-left">電話</th>
                    <th class="px-4 py-2 text-left">登録日</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($customers as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->id }}</td>
                        <td class="px-4 py-2">{{ $r->name }}</td>
                        <td class="px-4 py-2">{{ $r->email }}</td>
                        <td class="px-4 py-2">{{ $r->phone }}</td>
                        <td class="px-4 py-2">{{ $r->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">顧客の登録はまだありません<nav></nav></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->withQueryString()->links() }}</div>
@endsection
