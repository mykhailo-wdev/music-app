<template>
    <div class="app-wrap">
        <music-header></music-header>

        <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
                <component :is="Component" />
            </transition>
        </router-view>

    </div>
    
</template>

<script setup>
import { ref, computed, watch  } from 'vue';
import MusicHeader from './components/MusicHeader.vue';
import MusicAlert from './components/MusicAlert.vue';
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
  },
  mounted() {
  this.loadUsers();
}

};
</script> -->

<style lang="scss">
@use './assets/styles/typography.scss'; 

.fade-enter-active, .fade-leave-active {
    transition: all 0.4s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    filter: blur(1rem);
}

.app-wrap {
    overflow: hidden;
    background-color: var(--palette-white);
}

</style>
