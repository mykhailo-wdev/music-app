import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./views/HomePage.vue";
import LoginPage from "./views/LoginPage.vue";
import RecoverPassword from "./views/RecoverPassword.vue";
import PlayerPage from "./views/PlayerPage.vue";
import NotFound from "./views/NotFound.vue";
import RegistrationPage from "./views/RegistrationPage.vue";
import ProfilePage from "./views/ProfilePage.vue";
import ResetPassword from "./views/ResetPassword.vue";

const router =  createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: HomePage,
            meta: { title: 'Pulsebox' }
        },
        {
            path: '/login',
            component: LoginPage,
            meta: { title: 'Вхід - Pulsebox' }
        },
        {
            path: '/register',
            component: RegistrationPage,
            meta: { title: 'Реєстрація - Pulsebox' }
        },
        {
            path: '/recover-password',
            component: RecoverPassword,
            meta: { title: 'Відновити пароль - Pulsebox' }
        },
        {
            path: '/player',
            component: PlayerPage,
            meta: { title: 'Плеєр - Pulsebox' }
        },
        {
            path: '/profile',
            component: ProfilePage,
            meta: { title: 'Профіль - Pulsebox' }
        },
        {
            path: '/reset-password',
            component: ResetPassword,
            meta: { title: 'Відновити пароль - Pulsebox' }
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
    next();
});


export default router