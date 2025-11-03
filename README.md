# 🎵 Full Stack Music App (Vue 3 + PHP + MySQL + Docker)

> 🧩 A learning **pet project** that demonstrates a full stack setup — **Vue 3 + PHP + MySQL**, containerized with **Docker Compose**.  
> This project is non-commercial and uses a public API (e.g., Jamendo) to showcase music search functionality.

---

## 🚀 Features

- 🔍 Search for music using a public API  
- 🎧 Music player (play, pause, skip tracks)  
- 👤 User registration and authentication  
- 💾 Data stored in a MySQL database  
- ⚙️ Search limit control stored in `localStorage`  
- 🔔 Alert displayed when the search limit is exceeded  
- 🧱 Fully isolated services running in Docker containers  

---

## 🏗️ Tech Stack

| Layer | Technology | Description |
|--------|-------------|-------------|
| **Frontend** | Vue 3 (Composition API), Vuex, Webpack, Sass | SPA built with Webpack |
| **Backend** | PHP 8 (Apache) | REST API, environment configuration via `.env` |
| **Database** | MySQL 5.7 | Stores users and music data |
| **Admin Tool** | phpMyAdmin | Web interface for database management |
| **Containerization** | Docker, Docker Compose | Manages all project services |

