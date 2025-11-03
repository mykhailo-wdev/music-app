import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./views/HomePage.vue";
import LoginPage from "./views/LoginPage.vue";
import RecoverPassword from "./views/RecoverPassword.vue";
import PlayerPage from "./views/PlayerPage.vue";
import NotFound from "./views/NotFound.vue";
import RegistrationPage from "./views/RegistrationPage.vue";
import ProfilePage from "./views/ProfilePage.vue";
import ResetPassword from "./views/ResetPassword.vue";
import VerifyEmail from "./views/VerifyEmail.vue";
import PlayList from "./views/PlayList.vue";
import PrivacyPolicy from "./views/PrivacyPolicy.vue";
import store from "./store";

const router =  createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: HomePage,
            meta: { title: 'Pulsebox', guestOnly: true }
        },
        {
            path: '/login',
            component: LoginPage,
            meta: { title: 'Вхід - Pulsebox', guestOnly: true }
        },
        {
            path: '/register',
            component: RegistrationPage,
            meta: { title: 'Реєстрація - Pulsebox', guestOnly: true }
        },
        {
            path: '/recover-password',
            component: RecoverPassword,
            meta: { title: 'Відновити пароль - Pulsebox', guestOnly: true }
        },
        {
            path: '/player',
            component: PlayerPage,
            meta: { title: 'Плеєр - Pulsebox', requiresAuth: true }
        },
        {
            path: '/profile',
            component: ProfilePage,
            meta: { title: 'Профіль - Pulsebox', requiresAuth: true }
        },
        {
            path: '/reset-password',
            component: ResetPassword,
            meta: { title: 'Відновити пароль - Pulsebox', guestOnly: true }
        },
        {
            path: '/verify-email',
            component: VerifyEmail,
            meta: { title: 'Верифікація - Pulsebox', guestOnly: true }
        },
        {
            path: '/playlists',
            component: PlayList,
            meta: { title: 'Плейлисти - Pulsebox', requiresAuth: true }
        },
        {
            path: '/privacy-policy',
            component: PrivacyPolicy,
            meta: { title: 'Політика конфіденційності - Pulsebox', guestOnly: true }
        },
        {   path: '/:pathMatch(.*)*', 
            name: 'NotFound', 
            component: NotFound,
            meta: { title: 'Сторінку не знайдено - Pulsebox' }
        },
    ],

})

router.beforeEach((to, from, next) => {
    const defaultTitle = 'Pulsebox'; 
    document.title = to.meta.title || defaultTitle;

    const isAuth = store.getters["isAuthenticated"];

    if (to.meta.requiresAuth && !isAuth) {
        return next("/login");    
    }

    if (to.meta.guestOnly && isAuth) {
        return next("/player");   
    }

    next();
});


export default router