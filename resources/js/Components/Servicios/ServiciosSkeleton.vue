<template>
    <div class="space-y-3">
        <!-- Header skeleton -->
        <div class="flex justify-between items-center mb-4">
            <div class="animate-pulse h-5 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
            <div class="animate-pulse h-8 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
        </div>

        <!-- Servicios skeleton -->
        <div v-for="i in 4" :key="i" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    <div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-2"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                    <div class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
            </div>
        </div>
    </div>
</template>
