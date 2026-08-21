<script setup lang="ts">
import UsersPage from '../../../../../app-modules/identity/resources/js/Pages/Users/Index.vue'

defineOptions({ inheritAttrs: false })
</script>

<template>
    <UsersPage v-bind="$attrs" />
</template>
