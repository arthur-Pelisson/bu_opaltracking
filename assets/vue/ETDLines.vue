<template>
    <div>
        <MenuBar :user="this.user"/>
        <ETDLineTable :etd="this.etd" :etd-lines="this.etdLines" :user="this.user"
                      :qty-modified-to-create-partial="this.qtyModifiedToCreatePartial"
                      :conversations-count="this.conversationsCount" :is-read-only="this.isReadOnly" :vendor-no="this.vendorNo" :etd-conversation-count="this.etdConversationCount"/>
    </div>
</template>

<script>
import "../styles/app.scss";
import MenuBar from "./components/MenuBar";
import ETDLineTable from "./components/ETDLineTable";

export default {
    name: "EtdLines",
    components: {
        MenuBar,
        ETDLineTable
    },
    props: {
        user: {type: Object, required: true},
        etd: {type: Object, required: true},
        etdLines: {type: Array, required: true},
        qtyModifiedToCreatePartial: {type: Number, required: true},
        conversationsCount: {type: Array, required: true},
        isReadOnly: {type: Boolean, required: true},
        vendorNo: {type: String, required: true},
        etdConversationCount: {type: Number, required: true},
    }
}
</script>