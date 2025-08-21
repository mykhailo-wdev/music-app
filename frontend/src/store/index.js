import { createStore } from 'vuex';
import search from "./modules/search";
import auth from "./modules/auth";
import playlist from "./modules/playlist";


export default createStore({
    modules: {
        search,
        auth,
        playlist
    }
})