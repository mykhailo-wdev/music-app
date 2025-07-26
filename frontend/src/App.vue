<template>
    <div class="container">
        <h1>Vue + PHP + MySQL (Docker)ііццц</h1>
        <p>Vasya</p>
        <!-- <button @click="loadUsers">Donw 222sss</button>
        <ul>
            <li v-for="user in users" :key="user.id">{{ user.name }}</li>
        </ul> -->
        <music-button type-btn="btn-hot" text="Click"></music-button>

        <music-button  type-btn="btn-fresh" text="Click"></music-button>

        <music-button  type-btn="btn-sunny" text="Click"></music-button>

        <music-button  type-btn="btn-sky" text="Click"></music-button>

    </div>
</template>

<script setup>
import { ref } from 'vue';
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
li {
    list-style: square;
}
</style>
