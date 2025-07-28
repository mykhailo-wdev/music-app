<template>
    <div class="app-wrap" id="app">
        <music-header></music-header>
        <main class="main-app">
            <div class="container">
                <music-button type-btn="btn-hot" text="Click"></music-button>

                <music-button  type-btn="btn-fresh" text="Click"></music-button>

                <music-button  type-btn="btn-sunny" text="Click"></music-button>

                <music-button  type-btn="btn-sky" text="Click"></music-button>

                <img :src="require('@/assets/images/screenshot.png')" alt="Скріншот">

            </div>
        </main>
    </div>
    
</template>

<script setup>
import { ref } from 'vue';
import MusicHeader from './components/MusicHeader.vue';
import MusicButton from './components/MusicButton.vue';




</script>

<!-- <script>
import axios from 'axios';

export default {
  data() {
    return {
      users: []
    };
  },
  methods: {
    loadUsers() {
      axios.get('http://localhost:8000/api/get-users.php')
        .then(res => {
          this.users = res.data;
        })
        .catch(err => console.error(err));
    }
  }
};
</script> -->

<style lang="scss">
@use './assets/styles/typography.scss'; 

.app-wrap {
    overflow: hidden;
    background-color: var(--palette-white);
    .main-app {
        height: calc(100vh - 100px);
        height: calc(100dvh - 100px);
        padding: var(--m-space-80) 0;
    }
}

@media (max-width: 576px) {
    .app-wrap {
        overflow: hidden;
        background-color: var(--palette-white);
        .main-app {
            height: calc(100vh - 120px);
            height: calc(100dvh - 120px);
            padding: var(--m-space-32) 0;
        }
    }
}
</style>
