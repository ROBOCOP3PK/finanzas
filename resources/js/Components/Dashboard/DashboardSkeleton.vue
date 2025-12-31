<template>
    <div class="space-y-4">
        <!-- Cards principales skeleton -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="animate-pulse">
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-2"></div>
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="animate-pulse">
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2"></div>
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-28"></div>
                </div>
            </div>
        </div>

        <!-- Resumen categorias skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse">
                <div class="flex justify-between items-center mb-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-32"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="flex-1">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-1"></div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
                        </div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="flex-1">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-1"></div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                        </div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-14"></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="flex-1">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16 mb-1"></div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                        </div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-12"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen mes skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-28 mb-4"></div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-14"></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-28"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-18"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ultimos movimientos skeleton -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-36 mb-4"></div>
                <div class="space-y-3">
                    <div v-for="i in 3" :key="i" class="flex items-center gap-3 py-2">
                        <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="flex-1">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-32 mb-1"></div>
                            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded w-20"></div>
                        </div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
