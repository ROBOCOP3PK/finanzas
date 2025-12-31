<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
        <div v-for="i in 5" :key="i" class="p-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div class="animate-pulse flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                <div class="flex-1">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                </div>
                <div class="text-right">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-1"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                </div>
            </div>
        </div>
    </div>
</template>
