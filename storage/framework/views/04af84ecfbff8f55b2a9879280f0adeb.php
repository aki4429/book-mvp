<?php $__env->startSection('page-title', '顧客一覧'); ?>

<?php $__env->startSection('body'); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-4 py-2"><?php echo e($r->id); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->name); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->email); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->phone); ?></td>
                        <td class="px-4 py-2"><?php echo e($r->created_at->format('Y-m-d')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">顧客の登録はまだありません<nav></nav></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($customers->withQueryString()->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/app_3/resources/views/admin/customers/index.blade.php ENDPATH**/ ?>