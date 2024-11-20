<template>
    <div>
        <b-navbar toggleable="md" type="dark" id="navbar">
            <LateralMenu :etd-screen-type="etdScreenType"/>
            <div class="logo" style="margin-right: 20px">
                <b-img src="/build/images/logo-opal-blanc.jpg" fluid alt="Logo Opal"></b-img>
            </div>
            <b-navbar-brand href="#" style="padding-top: 7px">Tracking Orders</b-navbar-brand>
            <b-navbar-nav class="ml-auto">
                <b-dropdown variant="primary" right>
                    <template #button-content>
                        {{ user.code }}
                    </template>
                    <b-dropdown-item href="/logout">Log Out</b-dropdown-item>
                </b-dropdown>
                <b-dropdown variant="primary" class="notifications" right @show="getLastETDsUpdated()" no-caret>
                    <template #button-content>
                        <i class="fas fa-bell"></i>
                    </template>
                    <template v-if="isLoadETDs">
                        <div class="d-flex justify-content-center my-3">
                            <b-spinner label="Loading..."></b-spinner>
                        </div>
                    </template>
                    <template v-else>
                        <template v-if="lastETDsUpdated.length > 0">
                            <div class="notifications--list">
                                <b-dropdown-item v-for="(lastETDUpdated, index) in lastETDsUpdated" :key="index"
                                                 @click="openETD(lastETDUpdated.id)">
                                    <span class="notifications--list--date-vendor">{{
                                            lastETDUpdated.etdDate | formatDate
                                        }} : {{ lastETDUpdated.vendor.no }}</span>
                                    <span class="notifications--list--timeago"><span>Waiting for approval : {{
                                            lastETDUpdated.notValidatedETDLinesCount
                                        }}</span><span>{{ lastETDUpdated.dateUpdate | fromFormatDate }}</span></span>
                                </b-dropdown-item>
                            </div>
                        </template>
                        <p v-else>No item updated</p>
                    </template>
                    <b-button
                        block
                        variant="primary"
                        class="notifications--button"
                        @click="getLastETDsUpdated()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </b-button>
                </b-dropdown>
            </b-navbar-nav>
        </b-navbar>
    </div>
</template>

<script>
import "../../styles/app.scss";
import {BAvatar} from 'bootstrap-vue/esm/components/avatar';
import {BDropdown, BDropdownItem} from 'bootstrap-vue/esm/components/dropdown';
import {BNavbar, BNavbarBrand, BNavbarNav, BNavbarToggle} from 'bootstrap-vue/esm/components/navbar';
import {BNavItem, BNavItemDropdown} from 'bootstrap-vue/esm/components/nav';
import {BButton, BCard, BCollapse, BImg, BSpinner} from "bootstrap-vue";
import LateralMenu from "./LateralMenu";
import * as API from "./../utils/api";

export default {
    name: 'MenuBar',
    components: {
        LateralMenu,
        BAvatar,
        BDropdown,
        BDropdownItem,
        BNavbar,
        BNavbarBrand,
        BNavbarNav,
        BNavbarToggle,
        BNavItem,
        BNavItemDropdown,
        BCollapse,
        BCard,
        BButton,
        BSpinner,
        BImg
    },
    props: {
        user: {
            type: Object,
            required: true,
        },
        etdScreenType : {type: String}
    },
    data() {
        return {
            lastETDsUpdated: [],
            isLoadETDs: true
        }
    },
    methods: {
        openETD(etdId) {
            window.location.href = '/etd/' + etdId + '/etdlines';
        },
        getLastETDsUpdated() {
            this.isLoadETDs = true;
            API.getLastETDsUpdated()
                .then((response) => {
                    this.lastETDsUpdated = response.data;
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.isLoadETDs = false;
                });
        }
    },
};
</script>

<style lang="scss" scoped>
@import "../../styles/app.scss";

#navbar {
    background-color: $main-color;
}

</style>