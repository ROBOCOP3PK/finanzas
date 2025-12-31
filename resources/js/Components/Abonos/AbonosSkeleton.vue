<template>
    <div class="space-y-4">
        <!-- Card resumen skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse">
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2"></div>
                <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
            </div>
        </div>

        <!-- Lista de abonos skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
            <div v-for="i in 4" :key="i" class="p-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div class="animate-pulse flex items-center justify-between">
                    <div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    </div>
                    <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                </div>
            </div>
        </div>
    </div>
</template>
