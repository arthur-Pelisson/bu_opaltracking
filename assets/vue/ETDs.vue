<template>
    <div>
        <MenuBar :user="this.user" :etdScreenType="this.etdScreenType"/>
        <ETDTable :etds="this.etds" :user="this.user" :etdScreenType="this.etdScreenType" :totalCount="this.totalCount">
            <template v-slot:filter-vendor>
                <slot name="filter-vendor"></slot>
            </template>
            <template v-slot:pagination>
                <slot name="pagination"></slot>
            </template>
            <template v-slot:header-etd>
                <slot name="header-etd"></slot>
            </template>
            <template v-slot:header-vendor>
                <slot name="header-vendor"></slot>
            </template>
        </ETDTable>
    </div>
</template>

<script>
import "../styles/app.scss";
import MenuBar from "./components/MenuBar";
import ETDTable from "./components/ETDTable";


export default {
    name: "Etds",
    components: {
        MenuBar,
        ETDTable
    },
    props: {
        user: {type: Object, required: true},
        etds: {type: Array, required: true},
        etdScreenType: {type: String, required: true},
        totalCount: {type: Number, required: true}
    },
}
</script>

<style lang="scss" scoped>
@import "../styles/app.scss";

</style>