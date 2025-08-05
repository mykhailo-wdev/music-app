<template>
    <div>
        <h1>REgister PAge</h1>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
const showAlert = ref(false);
const store = useStore();


onMounted(() => {
    store.commit('resetSearchCount');
});


</script>

<style lang="scss" scoped>

</style>