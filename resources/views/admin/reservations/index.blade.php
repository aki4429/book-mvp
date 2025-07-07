@extends('layouts.admin')
@section('page-title', '予約一覧')

@section('body')
    <h2 class="text-xl font-semibold mb-6">予約一覧</h2>
    <a href="{{ route('admin.reservations.create') }}"
   class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
    ＋ 新規作成
</a>


    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">顧客</th>
                    <th class="px-4 py-2 text-left">日時</th>
                    <th class="px-4 py-2 text-left">ステータス</th>
                    <th class="px-4 py-2 text-left">登録日</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($reservations as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->id }}</td>
                        <td class="px-4 py-2">{{ $r->customer->name }}</td>
                        <td class="px-4 py-2">
                            {{ $r->timeSlot->slot_date }}
                            {{ $r->timeSlot->start_time }}-{{ $r->timeSlot->end_time }}
                        </td>
                        <td class="px-4 py-2">
                            @php $color = [
                                'pending'=>'bg-yellow-100 text-yellow-800',
                                'confirmed'=>'bg-green-100 text-green-800',
                                'canceled'=>'bg-red-100 text-red-800',
                                'completed'=>'bg-gray-100 text-gray-800'][$r->status] ?? 'bg-gray-100'; @endphp
                            <span class="px-2 py-1 rounded {{ $color }}">{{ $r->status }}</span>
                        </td>
                        <td class="px-4 py-2">{{ $r->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">
              <a href="{{ route('admin.reservations.show',$r) }}"
                 class="text-blue-600 hover:underline">詳細</a>
                 <a href="{{ route('admin.reservations.edit',$r) }}" class="text-blue-600">編集</a>
<form action="{{ route('admin.reservations.destroy',$r) }}" method="POST" class="inline">
    @csrf @method('DELETE')
    <button class="text-red-600" onclick="return confirm('削除しますか？')">削除</button>
</form>

            </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">予約がありません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $reservations->withQueryString()->links() }}</div>
@endsection
