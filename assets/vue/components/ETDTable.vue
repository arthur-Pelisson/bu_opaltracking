<template>
    <div>
        <b-container fluid class="text-left">
            <b-row>
                <b-col>
                    <b-card no-body class="my-3 content-card-wrapper">
                        <div id="etdlinesCardHeader">
                        <b-row class="my-3">
                            <b-col md="12">
                                <h1>{{ getTitle() }}</h1>
                            </b-col>
                        </b-row>
                        <b-row class="action-bar mb-3">
                            <b-col md="auto" class="search-filters">
                                <slot v-if="user.type === 'PURCHASER'" name="filter-vendor"></slot>
                            </b-col>
                            <b-col md="auto" h-align="right" class="text-right">
<!--                                <button class="btn btn-secondary search-filters&#45;&#45;button btn-icon"><i-->
<!--                                    class="fas fa-file-download"></i> Import-->
<!--                                </button>-->
<!--                                <button class="btn btn-secondary search-filters&#45;&#45;button btn-icon"><i-->
<!--                                    class="far fa-file-excel"></i> Export-->
<!--                                </button>-->
                            </b-col>
                        </b-row>
                        </div>
                        <b-row>
                            <b-col md="12">
                                <vue-virtual-table
                                    class="etd-table"
                                    :config="fields"
                                    :data="etdsData"
                                    :height="heightTable"
                                    :itemHeight="75"
                                    :minWidth="1000"
                                    :enableExport="false"
                                    language="en"
                                    :selectable="true"
                                >
                                    <template slot-scope="scope" slot="actionsSlot" >
                                          <b-button-group class="actions">
                                            <button v-if="scope.row.countMessages > 0" class="btn btn-hover btn-action" v-b-tooltip.hover
                                                    v-on:click="openModal(scope.row)" title="ETD conversation"><i
                                                class="fas fa-comments"></i></button>
                                            <button v-else class="btn btn-hover btn-action" v-on:click="openModal(scope.row)" title="ETD conversation"><i
                                                class="far fa-comment"></i></button>
                                            <button class="btn btn-hover btn-action" title="Close ETD" v-b-tooltip.hover
                                                  v-if="isUserPurchaser() && scope.row.status !== 'CLOSED'"
                                                  v-on:click="openETDCloseModal(scope.row)"><i
                                                class="fas fa-lock"></i></button>
                                            <button class="btn btn-hover btn-action" title="Open ETD" v-b-tooltip.hover
                                                  v-else-if="isUserPurchaser() && scope.row.status === 'CLOSED'"
                                                  v-on:click="openETDOpenModal(scope.row)"><i
                                                class="fas fa-lock-open"></i></button>
                                            <button class="btn btn-hover btn-action" title="ETD documents" v-b-tooltip.hover
                                                  v-on:click="openETDFilesModal(scope.row)"><i
                                                class="fas fa-file"></i></button>
                                              <button class="btn btn-hover btn-action" title="Export as CSV" v-b-tooltip.hover v-on:click="exportETD(scope.row)"><i
                                                  class="far fa-file-excel"></i></button>
                                            <button class="btn btn-hover btn-action" title="Show ETDLines" v-b-tooltip.hover
                                                  v-on:click="redirectToETDLines(scope.row.id)"><i
                                                class="fas fa-eye"></i></button>
                                          </b-button-group>
                                    </template>
                                    <template slot-scope="scope" slot="etdDateSlot">
                                        <div style="white-space: nowrap; font-size:14px" :data-id="scope.row.id">{{ scope.row.etdDate |formatDate }}</div>
                                    </template>
                                    <template slot-scope="scope" slot="statusSlot">
                                        <BadgeStatus :content="scope.row.status" type-badge="ETDStatus"/>
                                    </template>
                                </vue-virtual-table>
                            </b-col>
                        </b-row>
                        <b-row id="etdlinesCardFooter">
                            <b-col>
                                <span class="badge badge-primary">Total : {{ this.totalCount }}</span>
                                <span class="badge badge-primary"><i class="fas fa-eye"></i> : {{ this.etds.length }}</span>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
            <slot name="pagination"></slot>
        </b-container>
        <b-modal v-model="showConversationModal" size="xl" scrollable class="modal-dialog-centered" hide-footer>
            <template #modal-title>
                {{ titleConversationModal }} - <button class="btn btn-hover btn-action" v-on:click="downloadConversation()"><i class="fas fa-file-download"></i></button>
            </template>
            <MessagingModal :etd="this.etdSelected" :is-read-only="this.etdSelected.closed" :user="this.user"/>
        </b-modal>
        <b-modal :title="titleETDModal" v-model="showETDModal" class="modal-dialog-centered" size="xs">
            <p>Please, add a note :</p>
            <b-form-textarea
                placeholder="Enter your text here..."
                v-model="contentMessageETDModal"
                rows="3"
                max-rows="6"
            ></b-form-textarea>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" @click="cancel()">
                    Cancel
                </b-button>
                <b-button v-if="isETDModalToClose" size="sm" variant="primary" @click="closeETD()">
                    Close ETD
                </b-button>
                <b-button v-else-if="!isETDModalToClose" size="sm" variant="primary" @click="openETD()">
                    Open ETD
                </b-button>
            </template>
        </b-modal>
        <b-modal :title="titleETDDocumentsModal" v-model="showETDDocumentsModal" size="xl" class="modal-dialog-centered" scrollable hide-footer>
            <ETDDocuments :etd="this.etdSelected" :user="this.user"/>
        </b-modal>
    </div>
</template>

<script>
import "../../styles/app.scss";
import MenuBar from "./MenuBar";
import {BButton} from 'bootstrap-vue/esm/components/button';
import {BInputGroup, BInputGroupAppend} from 'bootstrap-vue/esm/components/input-group';
import {BTable} from 'bootstrap-vue/esm/components/table';
import {BFormInput} from 'bootstrap-vue/esm/components/form-input';
import {BCard, BCol, BContainer, BFormCheckbox, BIcon, BModal, BRow, TooltipPlugin} from "bootstrap-vue";
import Vue from "vue";
import MessagingModal from "./MessagingModal";
import * as API from "./../utils/api";
import * as Utils from "./../utils/utils";
import {ETDScreenType, UserType} from "../utils/enum";
import BadgeStatus from "./BadgeStatus";
import ETDDocuments from "./ETDDocuments";
import VueVirtualTable from 'vue-virtual-table';
import { BButtonGroup } from 'bootstrap-vue'


Vue.use(TooltipPlugin)
export default {
    name: "ETDTable",
    components: {
        ETDDocuments,
        MessagingModal,
        MenuBar,
        BIcon,
        BModal,
        BButton,
        BTable,
        BCol,
        BContainer,
        BRow,
        BInputGroupAppend,
        BFormInput,
        BInputGroup,
        BFormCheckbox,
        BCard,
        BadgeStatus,
        BButtonGroup,
        TooltipPlugin,
        VueVirtualTable
    },
    props: {
        user: {type: Object, required: true},
        etds: {type: Array, required: true},
        etdScreenType: {type: String, required: true},
        totalCount: {type: Number, required: true}
    },
    data() {
        return {
            etdsData: [],
            fields: [],
            showConversationModal: false,
            etdSelected: false,
            titleConversationModal: null,
            contentMessageETDModal: null,
            titleETDModal: null,
            etdForModal: null,
            showETDModal: false,
            isETDModalToClose: true,
            titleETDDocumentsModal: null,
            showETDDocumentsModal: false,
            heightTable: 0
        }
    },
    created() {
        window.addEventListener("resize", this.calculateHeightTable);
    },
    destroyed() {
        window.removeEventListener("resize", this.calculateHeightTable);
    },
    beforeMount() {
        this.initTable();
    },
    mounted() {
        this.calculateHeightTable();
        this.addDoubleClickOnRows();
    },
    methods: {
        exportETD(etd) {
            API.getExportETD(etd.id)
                .then((response) => {
                    const fileName = response.headers['content-disposition'].split('filename=')[1].replaceAll('"','');
                    Utils.downloadFile(fileName, response.data);
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        addDoubleClickOnRows() {
            window.addEventListener('load', () => {
                const els = document.querySelectorAll('.item-line');
                els.forEach((item) => {
                    item.addEventListener('click', function (e) {
                        const etdId = item.querySelector('[data-id]').getAttribute('data-id');
                        window.location.href = '/etd/' + etdId + '/etdlines';
                    });
                });
            })
        },
        calculateHeightTable() {
            const headerHeight = document.querySelector('#etdlinesCardHeader').offsetHeight;
            const footerHeight = document.querySelector('#etdlinesCardFooter').offsetHeight;
            const paginationHeight = document.querySelector('.pagination-wrapper') ? document.querySelector('.pagination-wrapper').offsetHeight : 70;
            this.heightTable = window.innerHeight - (90 + headerHeight + footerHeight + paginationHeight + 32);
        },
        getTitle() {
            switch (this.etdScreenType) {
                case ETDScreenType.Active:
                    return 'Actives ETD'
                case ETDScreenType.Open:
                    return 'Opens ETD'
                case ETDScreenType.Archive:
                    return 'Archives ETD'
            }
        },
        getAllETDs() {
            window.location.href = '/' + window.location.search;
        },
        isUserPurchaser() {
            return this.user.type === UserType.Purchaser;
        },
        downloadConversation() {
            API.downloadConversation(this.etdSelected.id, null)
                .then((response) => {
                    Utils.downloadFile(this.titleConversationModal + '.pdf', response.data);
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        openModal(etd) {
            this.etdSelected = etd;
            this.titleConversationModal = etd.vendorNo + ' ' + Utils.getDateFormatted(etd.etdDate) + ' - Summary';
            this.showConversationModal = true;
        },
        openETDFilesModal(etd) {
            this.etdSelected = etd;
            this.titleETDDocumentsModal = 'ETD ' + Utils.getDateFormatted(etd.etdDate) + ' ' + etd.vendorNo + ' files';
            this.showETDDocumentsModal = true;
        },
        openETDOpenModal(data) {
            console.log("data : ", data.id);
            this.isETDModalToClose = false;
            this.etdForModal = data;
            this.titleETDModal = 'Open ETD ' + Utils.getDateFormatted(data.etdDate) + ' ' + data.vendorNo;
            this.showETDModal = true;
        },
        openETDCloseModal(data) {
            this.isETDModalToClose = true;
            this.etdForModal = data;
            this.titleETDModal = 'Close ETD ' + Utils.getDateFormatted(data.etdDate) + ' ' + data.vendorNo;
            this.showETDModal = true;
        },
        closeETD() {
            if (this.etdForModal.id !== null) {
                API.closeETD(this.etdForModal.id, this.contentMessageETDModal)
                    .then((response) => {
                        this.cleanETDModal();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        },
        cleanETDModal() {
            Vue.delete(this.etdsData, this.etdForModal.index);
            this.showETDModal = false;
            this.contentMessageETDModal = null;
            this.etdForModal = null;
        },
        openETD() {
            if (this.etdForModal.id !== null) {
                API.openETD(this.etdForModal.id, this.contentMessageETDModal)
                    .then((response) => {
                        this.cleanETDModal();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        },
        onRowSelected(item) {
            if (item.id !== null) {
                this.redirectToETDLines(item.id);
            }
        },
        redirectToETDLines(etdId) {
            window.location.href = '/etd/' + etdId + '/etdlines';
        },
        initTable() {
            switch (this.user.type) {
                case UserType.Vendor:
                    this.fields = [
                        {prop: '_action', name: ' ', actionName: "actionsSlot", width: 100},
                        {prop: '_action', name: 'ETD', actionName: "etdDateSlot"},
                        {prop: '_action', name: 'Status', actionName: "statusSlot"},
                        {prop: 'notValidatedETDLinesCount', name: 'To Validate', class: 'is-numeric'},
                        {prop: 'etdChangedETDLinesCount', name: 'ETD Changed', class: 'is-numeric'},
                        {prop: 'qtyChangedETDLinesCount', name: 'Qty Changed', class: 'is-numeric'},
                        {prop: 'shipByChangedETDLinesCount', name: 'Ship by Changed', class: 'is-numeric'},
                        {prop: 'totalETDLinesCount', name: 'Total', class: 'is-numeric'}
                    ];
                    break;
                case UserType.Purchaser:
                    this.fields = [
                        {prop: '_action', name: ' ', actionName: "actionsSlot", width: 100},
                        {prop: '_action', name: 'ETD', actionName: "etdDateSlot"},
                        {prop: 'vendorNo', name: 'Vendor', tdClass: 'test'},
                        {prop: '_action', name: 'Status', actionName: "statusSlot"},
                        {prop: 'notValidatedETDLinesCount', name: 'To Validate', class: 'is-numeric'},
                        {prop: 'etdChangedETDLinesCount', name: 'ETD Changed', class: 'is-numeric'},
                        {prop: 'qtyChangedETDLinesCount', name: 'Qty Changed', class: 'is-numeric'},
                        {prop: 'shipByChangedETDLinesCount', name: 'Ship by Changed', class: 'is-numeric'},
                        {prop: 'totalETDLinesCount', name: 'Total', class: 'is-numeric'}
                    ];
                    break;
            }
            this.etdsData = this.etds;
        }
    }
}
</script>

<style lang="scss" scoped>
.action-bar {
    display: flex;
    align-content: center;
    justify-content: space-between;

    .search-filters {
        display: flex;
        align-content: center;
        justify-content: flex-start;

        &--button {
            margin: 0 .5rem;
        }

        form {
            input[type="text"] {
                min-width: 400px;
            }   

        }
    }
}

</style>