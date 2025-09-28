import { createStore } from 'vuex';
import search from "./modules/search";
import auth from "./modules/auth";
import playlist from "./modules/playlist";
import player from './modules/player';
import favorites from './modules/favorites';


export default createStore({
    modules: {
        search,
        auth,
        playlist,
        player,
        favorites
    }
})