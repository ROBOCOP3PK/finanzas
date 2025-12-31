<template>
    <div class="space-y-4">
        <!-- Filtros skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse flex gap-3">
                <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex-1"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg w-24"></div>
            </div>
        </div>

        <!-- Lista de gastos skeleton -->
        <div class="space-y-3">
            <div v-for="i in 5" :key="i" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
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
    </div>
</template>
