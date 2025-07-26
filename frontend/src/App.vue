<template>
    <div class="container">
        <h1>Vue + PHP + MySQL (Docker)ііццц</h1>
        <p>Vasya</p>
        <button @click="loadUsers">Donw 222sss</button>
        <ul>
            <li v-for="user in users" :key="user.id">{{ user.name }}</li>
        </ul>
    </div>
</template>

<script>
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
</script>

<style lang="scss" scoped>
li {
    list-style: square;
}
</style>
