<template>
    <div>
        <b-button v-b-toggle.sidebar-backdrop class="button-hamburger">
            <i id="burgermenu" style="color:white; font-size: 30px;" class="fas fa-bars"/>
        </b-button>
        <b-sidebar
            id="sidebar-backdrop"
            backdrop
            shadow
            no-header
        >
            <div class="logo">
                <b-img src="/build/images/logo-opal.png" fluid alt="Logo Opal"></b-img>
            </div>
            <b-nav vertical class="navbar-lateral">
                <b-nav-item href="/" :class="isActive('ACTIVE')"><i class="far fa-calendar"/>Actives</b-nav-item>
                <b-nav-item href="/opens" :class="isActive('OPEN')"><i class="far fa-calendar"/>Opens</b-nav-item>
                <b-nav-item href="/archives" :class="isActive('ARCHIVE')"><i class="far fa-calendar"/>Archives</b-nav-item>
            </b-nav>
        </b-sidebar>
    </div>
</template>


<script>
import "../../styles/app.scss";
import Vue from 'vue';
import {BSidebar} from 'bootstrap-vue/esm/components/sidebar';
import {BButton} from 'bootstrap-vue/esm/components/button';
import {BImg, VBToggle} from 'bootstrap-vue';
import {BNav, BNavItem} from "bootstrap-vue/esm/components/nav";

Vue.directive('b-toggle', VBToggle);

export default {
    name: 'LateralMenu',
    components: {BSidebar, BButton, BNavItem, BNav, BImg},
    props: {
        etdScreenType : {type: String}
    },
    methods: {
        isActive(screenType) {
            if(screenType === this.etdScreenType) {
                return 'active';
            }
            return '';
        }
    },
}
</script>

<style scoped lang="scss">
@import "../../styles/app.scss";

.b-sidebar-body {
    .logo {
        padding: 2rem 0 2rem 28px;
        border-bottom: 1px solid $main-color-light;
        margin-bottom: 15px;
    }

    .navbar-lateral {
        .nav-link {
            padding: 1rem 2rem;
            color: $main-font-color;

            svg {
                margin-bottom: 5px;
                margin-right: 20px;
            }
        }

        .active {
            border-right: 3px solid #1890FF;
            background-color: #E6F7FF;
            color: #1890FF;
        }
    }
}

#burgermenu:hover {
    cursor: pointer;
    color: grey !important;
}
</style>