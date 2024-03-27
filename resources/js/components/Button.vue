<template>
    <div
        class="shadow relative h-10 px-4 rounded-lg bg-primary-500 text-white text-white flex items-center text-center cursor-pointer hover:opacity-75 transition"
        :class="{
            'bg-red-500': theme === 'danger',
            'bg-yellow-500': theme === 'warning',
            'bg-blue-500': theme === 'info',
            'bg-green-500': theme === 'success',
            'bg-gray-200 !text-gray-900 dark:bg-gray-700 dark:!text-gray-300': theme === 'gray',
            'pointer-events-none': loading,
        }"
    >
        <slot />
        <div class="absolute inset-0 bg-gray-500/75 dark:bg-gray-900/75 rounded-lg" v-if="loading"></div>
        <Loader class="absolute inset-0 m-auto text-white" v-if="loading" width="32" />
    </div>
</template>

<script setup>
defineProps({
    theme: String,
    loading: Boolean
});
</script>
