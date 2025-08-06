<template>
    <main class="main-app">
        <div class="container">
            <music-auth-form 
                :mode="mode" 
                @change-mode="mode = $event"
            ></music-auth-form>
        </div>
    </main>
</template>

<script setup>
import MusicAuthForm from '@/components/MusicAuthForm.vue';
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
const showAlert = ref(false);
const store = useStore();
const mode = ref('register')


onMounted(() => {
    store.commit('resetSearchCount');
});


</script>

<style lang="scss" scoped>
.wrap-center {
    height: 100vh;
    display: flex;
    align-items: center;
}

</style>