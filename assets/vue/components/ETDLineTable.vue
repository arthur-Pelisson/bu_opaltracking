<template>
    <div>
        <b-container fluid class="text-left">
            <b-row>
                <b-col>
                    <b-card no-body id="etdlinesCard" class="my-3 content-card-wrapper etdLines">
                        <div id="etdlinesCardHeader">
                            <b-row class="my-3" align-v="center">
                                <b-col md="auto">
                                    <button class="btn btn-secondary" @click="returnPreviousPage()"><i
                                        class="fas fa-arrow-left"></i></button>
                                </b-col>
                                <b-col md="auto">
                                    <h1>
                                        <template v-if="this.vendorNo && !isUserVendor()">{{ this.vendorNo }} -
                                        </template>
                                        ETD : {{ this.etd.etdDate | formatDate }}
                                    </h1>
                                </b-col>
                                <b-col md="auto">
                                <span v-if="this.etdConversationCount > 0" class="btn btn-secondary"
                                      @click="openETDConversationModal()"><i
                                    class="fas fa-comments"></i></span>
                                    <span v-else class="btn btn-secondary" @click="openETDConversationModal()"><i
                                        class="far fa-comment"></i></span>
                                </b-col>
                            </b-row>
                            <b-row class="my-3" align-v="center">
                                <b-col md="auto" class="small-padding">
                                    <b-input-group>
                                        <template #prepend>
                                            <b-input-group-text>
                                                <i class="fa fa-search"></i>
                                            </b-input-group-text>
                                        </template>
                                        <input type="text" v-model="filter.filterRow" class="form-control"
                                               style="width: 170px;"/>
                                    </b-input-group>
                                </b-col>
                                <b-col md="auto" class="small-padding">
                                    <b-form-checkbox v-if="!this.filter.areETDLineReadOnlyHidden"
                                                     v-model="filter.areETDLineReadOnlyHidden" name="check-button"
                                                     switch
                                                     @input="hideReadOnlyLine()">
                                        Only updatable
                                    </b-form-checkbox>
                                    <b-form-checkbox v-if="this.filter.areETDLineReadOnlyHidden"
                                                     v-model="filter.areETDLineReadOnlyHidden" name="check-button"
                                                     switch
                                                     @input="showReadOnlyLine()">
                                        Only updatable
                                    </b-form-checkbox>
                                </b-col>
                                <b-col md="auto" class="small-padding">
                                    <div class="etdStatus">
                                        <button class="btn btn-secondary btn-icon" @click="cleanFilterRow()">Filter by
                                            status
                                        </button>
                                        <b-form-checkbox-group
                                            v-model="filter.statusFilterSelected"
                                            :options="etdLineStatus"
                                            stacked
                                            class="etdStatusList lg"
                                        ></b-form-checkbox-group>
                                    </div>

                                </b-col>
                                <b-col md="auto" class="small-padding">
                                    <div class="tagFilters">
                                        <button class="btn btn-secondary btn-icon" @click="cleanFilterRow()">Filter by
                                            tags
                                        </button>
                                        <b-form-checkbox-group
                                            v-model="filter.tagsFilterSelected"
                                            :options="etdLineTags"
                                            stacked
                                            class="tagFiltersList"
                                        ></b-form-checkbox-group>
                                    </div>
                                </b-col>
                                <b-col md="auto" class="small-padding">
                                    <button class="btn btn-secondary btn-icon" @click="cleanFilterRow()">Clear filters
                                    </button>
                                </b-col>
                                <b-col md="auto" class="small-padding">
                                    <button v-if="!areAllRowsSelected()" class="btn btn-secondary btn-icon"
                                            @click="selectAllRows()">Select all
                                    </button>
                                    <button v-else class="btn btn-secondary btn-icon" @click="unselectAllRows()">
                                        Unselect all
                                    </button>
                                </b-col>
                                <b-col h-align="right" v-if="isUserEnabledToUpdate()" class="text-right">
                                    <b-dropdown id="dropdown-dropdown" dropdown text="Actions on selected"
                                                variant="secondary"
                                                :disabled="this.etdLinesSelected.length === 0">
                                        <b-dropdown-item v-if="isUserPurchaser()" @click="approveSelected()">Approve
                                            selected
                                        </b-dropdown-item>
                                        <b-dropdown-item v-if="isUserPurchaser()" @click="rejectSelected()">Reject
                                            selected
                                        </b-dropdown-item>
                                        <b-dropdown-item @click="showETDDateModal = true">Change ETD date
                                        </b-dropdown-item>
                                        <b-dropdown-item @click="showShipByModal = true">Change ship by
                                        </b-dropdown-item>
                                    </b-dropdown>
                                    <b-dropdown
                                        split
                                        split-variant="primary"
                                        variant="primary"
                                        text="Validate changes"
                                        class="m-2"
                                        @click="validateConfirmation()"
                                    >
                                        <b-dropdown-item @click="cancelChanges()">Cancel changes</b-dropdown-item>
                                    </b-dropdown>
                                </b-col>
                            </b-row>
                        </div>
                        <b-row>
                            <b-col md="12">
                                <vue-virtual-table
                                    :config="fields"
                                    :data="etdLinesDisplayed"
                                    :height="heightTable"
                                    :itemHeight="48"
                                    :minWidth="1000"
                                    language="en"
                                    :selectable="false"
                                >
                                    <template slot-scope="scope" slot="actionsSlot">
                                        <div>
                                            <button v-if="scope.row.countMessages > 0" title="ETD Line conversation"
                                                    v-b-tooltip.hover
                                                    @click="openETDLineConversationModal(scope.row)"
                                                    :disabled="scope.row.id === 0"
                                                    class="btn btn-hover btn-action" v-html="getIconComment(true)">
                                            </button>
                                            <button v-else @click="openETDLineConversationModal(scope.row)"
                                                    title="ETD Line conversation" v-b-tooltip.hover
                                                    :disabled="scope.row.id === 0"
                                                    class="btn btn-hover btn-action" v-html="getIconComment(false)">
                                            </button>
                                            <button v-if="isUserPurchaser()" :disabled="!isETDLineApprovable(scope.row)"
                                                    @click="approveETDLine(scope.row)"
                                                    class="btn btn-hover btn-action"><i
                                                class="fas fa-check"
                                                style="color:#52C41A"></i></button>
                                            <button v-if="isUserPurchaser()" :disabled="!isETDLineRejectable(scope.row)"
                                                    @click="rejectETDLine(scope.row)"
                                                    class="btn btn-hover btn-action"><i
                                                class="fas fa-times"
                                                style="color:#F5222D"></i></button>
                                        </div>
                                    </template>
                                    <template slot-scope="scope" slot="checkedSlot">
                                        <input type="checkbox" v-model="scope.row.checked"
                                               :ref="`input-checked-${scope.row._index}`"
                                               @change="changeETDLineChecked(scope.row, null, true)">
                                    </template>
                                    <template slot-scope="scope" slot="statusSlot">
                                        <BadgeStatus :content="scope.row.status" type-badge="ETDLineStatus"/>
                                    </template>
                                    <template slot-scope="scope" slot="outstandingQtySlot">
                                        {{ scope.row.outstandingQty | formatQuantity }}
                                    </template>
                                    <template slot-scope="scope" slot="deliveryDateSlot">
                                        {{ scope.row.deliveryDate | formatDate }}
                                    </template>
                                    <template slot-scope="scope" slot="etdLineTagsSlot">
                                        <div class="badge-list">
                                            <BadgeStatus v-if="scope.row.etdLineTag.partial" content="Partial"
                                                         type-badge="ETDLineTag"/>
                                            <BadgeStatus v-if="scope.row.etdLineTag.completed" content="Completed"
                                                         type-badge="ETDLineTag"/>
                                            <BadgeStatus v-if="scope.row.etdLineTag.closed" content="Closed"
                                                         type-badge="ETDLineTag"/>
                                            <BadgeStatus v-if="scope.row.etdLineTag.etdChanged" content="ETDChanged"
                                                         type-badge="ETDLineTag"/>
                                            <BadgeStatus v-if="scope.row.etdLineTag.shipByChanged"
                                                         content="ShipByChanged"
                                                         type-badge="ETDLineTag"/>
                                            <BadgeStatus v-if="scope.row.etdLineTag.qtyChanged" content="QtyChanged"
                                                         type-badge="ETDLineTag"/>
                                        </div>
                                    </template>
                                    <template slot-scope="scope" slot="etdDateConfirmedSlot">
                                        <div v-if="isRowEditable(scope.row)" class="is-calendar datepicker">
                                            <b-form-datepicker
                                                :disabled="isPartialAvailable(scope.row)"
                                                v-model="scope.row.etdDateConfirmed"
                                                button-only
                                                right
                                                locale="en-US"
                                                hide-header
                                                :min="scope.row.etdDate"
                                                :reset-button="isETDDateNullable(scope.row)"
                                                :reset-value=null
                                                label-reset-button="Cancel balance"
                                                :id="'datepicker_' + scope.row._index"
                                                @input="updateETDDateConfirmed(scope.row)"
                                                title="Change date" v-b-tooltip.hover
                                            ></b-form-datepicker>
                                            <input
                                                v-model="scope.row.etdDateConfirmed"
                                                type="text"
                                                readonly
                                                @click="openCalendar('datepicker_' + scope.row._index)"
                                                :disabled="isPartialAvailable(scope.row)"
                                                placeholder="Balance cancelled"
                                            />
                                        </div>
                                        <template v-else>
                                            <template v-if="isDateNull(scope.row.etdDateConfirmed)">
                                                Balance cancelled
                                            </template>
                                            <template>
                                                {{
                                                    scope.row.etdDateConfirmed | formatDate
                                                }}
                                            </template>
                                        </template>
                                    </template>
                                    <template slot-scope="scope" slot="outstandingQtyConfirmedSlot">
                                        <template v-if="isRowEditable(scope.row)">
                                            <div class="form-group">
                                                <button v-if="isPartialAvailable(scope.row)"
                                                        title="Manage partial" v-b-tooltip.hover
                                                        @click="openPartialModal(scope.row)"
                                                        class="btn btn-hover btn-action"><i
                                                    class="fas fa-layer-group"></i></button>
                                                <input class="form-control" type="number"
                                                       :readonly="isPartialAvailable(scope.row)"
                                                       :value="scope.row.outstandingQtyConfirmed"
                                                       step="1" min="0"
                                                       @change="checkOutstandingQtyConfirmed(scope.row, $event)"/>
                                            </div>
                                        </template>
                                        <template v-else>{{
                                                scope.row.outstandingQtyConfirmed  | formatQuantity
                                            }}
                                        </template>
                                    </template>
                                    <template slot-scope="scope" slot="shipByConfirmedSlot">
                                        <select v-if="isRowEditable(scope.row)" v-model="scope.row.shipByConfirmed"
                                                :disabled="isPartialAvailable(scope.row)"
                                                @change="updateShipByConfirmed(scope.row)"
                                                class="custom-select btn-sm">
                                            <template v-for="shipByOption in shipByOptions">
                                                <option :value="shipByOption.value">{{ shipByOption.text }}</option>
                                            </template>
                                        </select>
                                        <template v-else>{{ scope.row.shipByConfirmed }}</template>
                                    </template>
                                    <template slot-scope="scope" slot="availableQtySlot">
                                        {{ scope.row.availableQty | formatQuantity }}
                                    </template>
                                    <template slot-scope="scope" slot="commentsSlot">
                                        <template v-if="scope.row.comments !== null && scope.row.comments !== ''">
                                            <b-button :id="`popover-${scope.row._index}`" variant="primary"
                                                      class="btn btn-hover btn-action"><i class="fas fa-info"></i>
                                            </b-button>
                                            <b-popover
                                                :target="`popover-${scope.row._index}`"
                                                triggers="hover focus"
                                            >
                                                {{ scope.row.comments }}
                                            </b-popover>
                                        </template>
                                    </template>
                                </vue-virtual-table>
                            </b-col>
                        </b-row>
                        <b-row id="etdlinesCardFooter">
                            <b-col>
                                <span class="badge badge-primary">Total : {{ this.countETDLines }}</span>
                                <span class="badge badge-primary"><i
                                    class="fas fa-eye"></i> : {{ this.countETDLinesDisplayed }}</span>
                            </b-col>
                            <b-col>
                                <div style="font-size:12px; font-style: italic; text-align: right; margin-top: 10px;">The changes are stored temporarily in your browser. They are not counted until they are validated</div>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
        </b-container>
        <b-modal v-model="showETDLineConversationModal" size="xl"
                 class="modal-dialog-centered" scrollable
                 hide-footer>
            <template #modal-title>
                {{ titleConversationModal }} -
                <button class="btn btn-hover btn-action" v-on:click="downloadConversation(true)"><i
                    class="fas fa-file-download"></i></button>
            </template>
            <MessagingModal :etd="this.etd" :etd-line="this.etdLineSelected" :is-read-only="isETDClosed()"
                            :user="this.user"/>
        </b-modal>
        <b-modal v-model="showETDConversationModal" size="xl"
                 class="modal-dialog-centered" scrollable hide-footer>
            <template #modal-title>
                {{ titleConversationModal }} -
                <button class="btn btn-hover btn-action" v-on:click="downloadConversation()"><i
                    class="fas fa-file-download"></i></button>
            </template>
            <MessagingModal :etd="this.etd" :is-read-only="isETDClosed()" :user="this.user"/>
        </b-modal>
        <b-modal title="Cancel updates" v-model="showCancelUpdateModal" size="xs" class="modal-dialog-centered"
                 @ok="validateCancelChanges()">
            <p><i class="fas fa-exclamation-triangle"></i>Are you sure to cancel all updates ?</p>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" @click="cancel()">
                    Return to updates
                </b-button>
                <b-button size="sm" variant="primary" @click="ok()">
                    Cancel (no rollback)
                </b-button>
            </template>
        </b-modal>
        <b-modal title="Update summary" v-model="showValidationModal" size="xl" class="modal-dialog-centered"
                 @ok="validateUpdate()">
            <p><i class="fas fa-exclamation-triangle"></i>If you validate, you won't be able to update your ETD lines !
            </p>
            <div id="summary-validation-modal">
                <p v-if="this.countApprovedChanged > 0">Approved : {{ this.countApprovedChanged }}</p>
                <p v-if="this.countRejectedChanged > 0">Rejected : {{ this.countRejectedChanged }}</p>
                <p v-if="this.countValidateShipByChanged > 0">Ship By Changed : {{
                        this.countValidateShipByChanged
                    }}</p>
                <p v-if="this.countValidateETDDateChanged > 0">ETD Date Changed : {{
                        this.countValidateETDDateChanged
                    }}</p>
                <p v-if="this.countValidateQtyChanged > 0">Qty Changed : {{ this.countValidateQtyChanged }}</p>
            </div>
            <p>You can add some explanations :</p>
            <textarea placeholder="Enter your text here..." v-model="contentMessageValidationModal" rows="6"
                      class="form-control" wrap="soft">
            </textarea>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" variant="primary" @click="ok()">
                    Validate (no rollback)
                </b-button>
            </template>
        </b-modal>
        <b-modal title="Change ETD date on selected ETD lines" v-model="showETDDateModal" class="modal-dialog-centered"
                 size="xs"
                 @ok="handleMultipleETDDateUpdate()">
            <div class="datepicker">
                <b-form-datepicker
                    v-model="dateSelectedETDDateModal"
                    button-only
                    right
                    locale="en-US"
                    hide-header
                    :min="this.etd.etdDate"
                    aria-controls="inputHandleMultipleETDDateUpdate"
                    id="datepickerHandleMultipleETDDateUpdate"
                ></b-form-datepicker>
                <input
                    id="inputHandleMultipleETDDateUpdate"
                    @click="openCalendar('datepickerHandleMultipleETDDateUpdate')"
                    v-model="dateSelectedETDDateModal"
                    type="text"
                    readonly
                    placeholder="Balance cancelled"
                />
            </div>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" @click="cancel()">
                    Cancel
                </b-button>
                <b-button size="sm" variant="primary" @click="ok()">
                    Apply
                </b-button>
            </template>
        </b-modal>
        <b-modal title="Change Ship By on selected ETD lines" v-model="showShipByModal" class="modal-dialog-centered"
                 size="xs"
                 @ok="handleMultipleShipByUpdate()">
            <select v-model="shipBySelectedShipByModal" class="custom-select btn-sm">
                <template v-for="shipByOption in shipByOptions">
                    <option :value="shipByOption.value">{{ shipByOption.text }}</option>
                </template>
            </select>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" @click="cancel()">
                    Cancel
                </b-button>
                <b-button size="sm" variant="primary" @click="ok()">
                    Apply
                </b-button>
            </template>
        </b-modal>
        <b-modal :title="titlePartialModal" v-model="showPartialModal" size="lg" class="modal-dialog-centered"
                 :no-close-on-esc=true
                 :no-close-on-backdrop=true :ok-only=true :hide-header-close=true @cancel="cancelPartial()">
            <p>If you want to close a partial, let the ETD date empty !</p>
            <b-alert :show="this.isETDLinePartialQtyTooMuch" variant="danger">The total quantity is greater than
                expected
            </b-alert>
            <b-alert :show="this.isETDLinePartialQtyTooLow" variant="danger">The total quantity is insufficient
            </b-alert>
            <b-alert :show="this.isAtLeastOnePartialQtyTooLow" variant="danger">Quantity cannot be lower than
                {{ this.qtyModifiedToCreatePartial }}, please remove the ETD date
            </b-alert>
            <b-alert :show="this.isETDLineTwoClosed" variant="danger">Please regroup partials without ETD
            </b-alert>
            <b-alert :show="this.isETDLinePartialDuplicated" variant="danger">2 partials cannot have the same ETD date
                and the same ship by
            </b-alert>
            <template v-if="etdLinePartial !== null">
                <b-row>
                    <b-col md="4" class="datepicker" v-if="isRowEditable(etdLinePartial)">
                        <b-form-datepicker
                            v-model="etdLinePartial.etdDateConfirmed"
                            button-only
                            right
                            locale="en-US"
                            hide-header
                            :min="etdLinePartial.etdDate"
                            aria-controls="etdLinePartialParentInput"
                            id="datepickerpartial_parent"
                            @input="updatePartialETDDateConfirmed()"
                        ></b-form-datepicker>
                        <input
                            id="etdLinePartialParentInput"
                            @click="openCalendar('datepickerpartial_parent')"
                            v-model="etdLinePartial.etdDateConfirmed"
                            type="text"
                            readonly
                            placeholder="Balance cancelled"
                        />
                    </b-col>
                    <b-col v-else style="text-align: right">
                        <template v-if="isDateNull(etdLinePartial.etdDateConfirmed)">
                            Balance cancelled
                        </template>
                        <template>
                            {{
                                etdLinePartial.etdDateConfirmed | formatDate
                            }}
                        </template>
                    </b-col>
                    <b-col md="2">
                        <input type="number" size="sm" class="form-control" step="1" min="0"
                               v-if="isRowEditable(etdLinePartial)"
                               v-model="etdLinePartial.outstandingQtyConfirmed"
                               @change="updatePartialOutstandingQtyConfirmed()"/>
                        <div style="text-align: right" v-else>{{
                                etdLinePartial.outstandingQtyConfirmed | formatQuantity
                            }}
                        </div>
                    </b-col>
                    <b-col md="4">
                        <select v-model="etdLinePartial.shipByConfirmed" v-if="isRowEditable(etdLinePartial)"
                                @change="updatePartialShipByConfirmed(etdLinePartial)"
                                class="custom-select btn-sm">
                            <template v-for="shipByOption in shipByOptions">
                                <option :value="shipByOption.value">{{ shipByOption.text }}</option>
                            </template>
                        </select>
                        <div v-else>{{ etdLinePartial.shipByConfirmed }}</div>
                    </b-col>
                    <b-col md="2">
                        <span class="btn btn-secondary" v-if="etdLinePartial.childETDLines.length === 0"
                              @click="addEtdLineChildPartial()"><i class="fas fa-plus"></i></span>
                    </b-col>
                </b-row>
                <b-row v-for="(child, index) in etdLinePartial.childETDLines" :key="index" class="my-3">
                    <b-col md="4" class="datepicker">
                        <b-form-datepicker
                            v-model="child.etdDateConfirmed"
                            button-only
                            right
                            locale="en-US"
                            hide-header reset-button
                            placeholder="Balance cancelled"
                            label-reset-button="Cancel balance"
                            :reset-value=null
                            :disabled="isPartialETDDisable(child)"
                            :min="etdLinePartial.etdDate"
                            :aria-controls="child._index"
                            :id="'datepickerpartial_child' + index"
                            @input="updatePartialETDDateConfirmed()"
                        ></b-form-datepicker>
                        <input
                            :id="child._index"
                            @click="openCalendar('datepickerpartial_child' + index)"
                            v-model="child.etdDateConfirmed"
                            :disabled="isPartialETDDisable(child)"
                            type="text"
                            readonly
                            placeholder="Balance cancelled"
                        />
                    </b-col>
                    <b-col md="2">
                        <input type="number" class="form-control" size="sm" step="1" min="0"
                               v-model="child.outstandingQtyConfirmed"
                               :max="child.outstandingQty | formatQuantity"
                               @change="updatePartialOutstandingQtyConfirmed()"/>
                    </b-col>
                    <b-col md="4">
                        <select v-model="child.shipByConfirmed"
                                @change="updatePartialShipByConfirmed(child)"
                                class="custom-select btn-sm">
                            <template v-for="shipByOption in shipByOptions">
                                <option :value="shipByOption.value">{{ shipByOption.text }}</option>
                            </template>
                        </select>
                    </b-col>
                    <b-col md="2">
                        <b-button-group>
                            <b-button v-if="index === etdLinePartial.childETDLines.length - 1"
                                      @click="addEtdLineChildPartial()"
                                      class="btn btn-hover"><i class="fas fa-plus"></i></b-button>
                            <b-button @click="removeEtdLineChildPartial(child)" class="btn btn-hover"><i
                                class="fas fa-minus"></i></b-button>
                        </b-button-group>

                    </b-col>
                </b-row>
                <textarea placeholder="Please explain us why you need to create a partial"
                          v-model="contentMessagePartialModal" rows="6" class="form-control" wrap="soft"></textarea>
            </template>
            <template #modal-footer="{ ok, cancel }">
                <b-button size="sm" variant="primary" @click="cancel()">
                    Cancel updates
                </b-button>
                <b-button size="sm" variant="primary" @click="applyUpdateETDPartials()"
                          :disabled="isApplyPartialDisabled()">
                    Apply partial
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import {BButton} from 'bootstrap-vue/esm/components/button';
import {BInputGroup, BInputGroupAppend} from 'bootstrap-vue/esm/components/input-group';
import {BTable} from 'bootstrap-vue/esm/components/table';
import {BFormInput} from 'bootstrap-vue/esm/components/form-input';
import {
    BAlert,
    BButtonGroup,
    BButtonToolbar,
    BCard,
    BCol,
    BContainer,
    BDropdownItem,
    BFormCheckbox,
    BFormCheckboxGroup,
    BFormDatepicker,
    BIcon,
    BInputGroupText,
    BModal,
    BPagination,
    BRow
} from "bootstrap-vue";
import {BDropdown} from "bootstrap-vue/esm/components/dropdown";
import MenuBar from "./MenuBar";
import MessagingModal from "./MessagingModal";
import * as Utils from "./../utils/utils";
import * as API from "./../utils/api";
import {ETDLineStatus, ETDLineTag, ETDStatus, UserType} from "../utils/enum";
import BadgeStatus from "./BadgeStatus";
import VueVirtualTable from 'vue-virtual-table';

export default {
    name: "EtdLines",
    components: {
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
        BDropdown,
        BDropdownItem,
        BAlert,
        BFormCheckbox,
        BButtonGroup,
        BButtonToolbar,
        BCard,
        BInputGroupText,
        BFormCheckboxGroup,
        BFormDatepicker,
        BPagination,
        BadgeStatus,
        VueVirtualTable
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
        previousePage: {type: String, required: true},
    },
    data() {
        return {
            etdLinesData: [],
            etdLinesDataInitSave: [],
            etdLinesSelected: [],
            etdLineSelected: [],
            fields: [],
            shipByOptions: [
                {value: 'AIR', text: 'AIR'},
                {value: 'EXPRESS', text: 'EXPRESS'},
                {value: 'HKONG', text: 'HONG KONG FAIR'},
                {value: 'MIDO', text: 'MIDO'},
                {value: 'ROUTE', text: 'ROUTE'},
                {value: 'SEA', text: 'SEA'},
                {value: 'SEAAIR', text: 'SEA / AIR'},
                {value: 'SILMO', text: 'SILMO'},
            ],
            showETDLineConversationModal: false,
            showETDConversationModal: false,
            titleConversationModal: null,
            showETDDateModal: false,
            dateSelectedETDDateModal: Utils.getDateFormatted(this.etd.etdDate),
            showShipByModal: false,
            shipBySelectedShipByModal: null,
            showValidationModal: false,
            countValidateShipByChanged: 0,
            countValidateETDDateChanged: 0,
            countValidateQtyChanged: 0,
            countApprovedChanged: 0,
            countRejectedChanged: 0,
            contentMessageValidationModal: null,
            showPartialModal: false,
            titlePartialModal: null,
            etdLinePartial: null,
            isETDLinePartialQtyTooMuch: false,
            isETDLinePartialDuplicated: false,
            isETDLinePartialQtyTooLow: false,
            filter: {
                filterRow: '',
                statusFilterSelected: [ETDLineStatus.Approved, ETDLineStatus.Rejected, ETDLineStatus.Initial, ETDLineStatus.WaitingForApproval],
                tagsFilterSelected: [ETDLineTag.Closed, ETDLineTag.Partial, ETDLineTag.Completed, ETDLineTag.ShipByChanged, ETDLineTag.ETDChanged, ETDLineTag.QtyChanged],
                areETDLineReadOnlyHidden: true,
            },
            countETDLinesDisplayed: 0,
            countETDLines: 0,
            showCancelUpdateModal: false,
            etdLinePartialSave: null,
            etdLinePartialOutstandingQtyOpenModal: null,
            isAllChecked: false,
            isAtLeastOnePartialQtyTooLow: false,
            isETDLineTwoClosed: false,
            currentPage: 1,
            heightTable: 0,
            etdLineStatus: [
                {value: ETDLineStatus.Approved, text: 'Approved'},
                {value: ETDLineStatus.Rejected, text: 'Rejected'},
                {value: ETDLineStatus.Initial, text: 'Initial'},
                {value: ETDLineStatus.WaitingForApproval, text: 'Waiting for approval'},
            ],
            etdLineTags: [
                {value: ETDLineTag.Closed, text: 'Closed'},
                {value: ETDLineTag.Partial, text: 'Partial'},
                {value: ETDLineTag.Completed, text: 'Completed'},
                {value: ETDLineTag.ShipByChanged, text: 'ShipByChanged'},
                {value: ETDLineTag.ETDChanged, text: 'ETDChanged'},
                {value: ETDLineTag.QtyChanged, text: 'QtyChanged'},
            ],
            contentMessagePartialModal: null,
            partialMessages: [],
        }
    },
    mounted() {
        this.initTable();
        this.calculateHeightTable();
        console.log(this.previousePage);
    },
    created() {
        window.addEventListener("resize", this.calculateHeightTable);
    },
    destroyed() {
        window.removeEventListener("resize", this.calculateHeightTable);
    },
    computed: {
        etdLinesDisplayed() {
            return this.getETDLinesFiltered();
        }
    },
    methods: {
        downloadConversation(isETDLine = false) {
            let apiCall = null;
            if (isETDLine) {
                apiCall = API.downloadConversation(this.etd.id, this.etdLineSelected.id);
            } else {
                apiCall = API.downloadConversation(this.etd.id, null);
            }
            apiCall.then((response) => {
                Utils.downloadFile(this.titleConversationModal + '.pdf', response.data);
            })
                .catch((error) => {
                    console.log(error);
                });
        },
        openCalendar(index) {
            document.querySelector('#' + index).dispatchEvent(new Event("click"));
        },
        calculateHeightTable() {
            const headerHeight = document.querySelector('#etdlinesCardHeader').offsetHeight;
            const footerHeight = document.querySelector('#etdlinesCardFooter').offsetHeight;
            this.heightTable = window.innerHeight - (90 + headerHeight + footerHeight + 32);
        },
        getIconComment(isMultipleComments) {
            if (isMultipleComments) {
                return '<i class="fas fa-comments"></i>';
            }
            return '<i class="far fa-comment"></i>';
        },
        updateCountsUpdates() {
            this.countValidateShipByChanged = 0;
            this.countValidateETDDateChanged = 0;
            this.countValidateQtyChanged = 0;
            this.countApprovedChanged = 0;
            this.countRejectedChanged = 0;

            this.etdLinesData.forEach((item, index) => {
                let etdLineOriginal = null;

                if (item.parentETDLine !== null && item.navisionLineNo === 0) {
                    // Pour le cas des childs crées sur le front et qui n'ont pas encore été généré en back, on regarde par rapport au parent
                    etdLineOriginal = this.etdLinesDataInitSave.find(_ => _.navisionLineNo === item.parentETDLine.navisionLineNo && _.navisionDocNo === item.navisionDocNo);
                } else {
                    etdLineOriginal = this.etdLinesDataInitSave.find(_ => _.navisionLineNo === item.navisionLineNo && _.navisionDocNo === item.navisionDocNo);
                    // TODO Faire attention aux count à la fois sur les parents et les enfants !
                    if (etdLineOriginal.outstandingQtyConfirmed !== item.outstandingQtyConfirmed) {
                        this.countValidateQtyChanged++;
                    }
                }

                if (etdLineOriginal.shipByConfirmed !== item.shipByConfirmed) {
                    this.countValidateShipByChanged++;
                }
                if (etdLineOriginal.etdDateConfirmed !== item.etdDateConfirmed) {
                    this.countValidateETDDateChanged++;
                }
                if (etdLineOriginal.status !== item.status) {
                    if (item.status === ETDLineStatus.Approved) {
                        this.countApprovedChanged++;
                    } else if (item.status === ETDLineStatus.Rejected) {
                        this.countRejectedChanged++;
                    }
                }

            })
        },
        cleanFilterRow() {
            this.filter.filterRow = '';
            this.filter.statusFilterSelected = [];
            this.filter.tagsFilterSelected = [];
        },
        isAtLeastOneLineChanged() {
            this.updateCountsUpdates();
            return !(this.countValidateShipByChanged === 0 && this.countValidateETDDateChanged === 0 && this.countValidateQtyChanged === 0 && this.countApprovedChanged === 0 && this.countRejectedChanged === 0);
        },
        validateConfirmation() {
            this.updateCountsUpdates();
            this.showValidationModal = true;
        },
        validateUpdate() {
            const message = this.contentMessageValidationModal;
            const onlyParentETDLine = this.formatETDLinesForUpdating();
            const counters = {
                countApprovedChanged: this.countApprovedChanged,
                countRejectedChanged: this.countRejectedChanged,
                countValidateETDDateChanged: this.countValidateETDDateChanged,
                countValidateQtyChanged: this.countValidateQtyChanged,
                countValidateShipByChanged: this.countValidateShipByChanged
            }

            API.updateETDLines(this.etd.id, onlyParentETDLine, message, counters, this.partialMessages)
                .then((response) => {
                    if (response.status === 204) {
                        this.contentMessageValidationModal = null;
                        this.cleanLocalStorage();
                        window.location.reload();
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
            this.showValidationModal = false;
        },

        //region Partials
        openPartialModal(etdLine) {
            this.etdLinePartial = null;
            if (etdLine.parentETDLine === null) {
                this.etdLinePartial = etdLine;
            } else {
                this.etdLinePartial = this.etdLinesData.find(_ => _.itemReference === etdLine.itemReference && _.navisionDocNo === etdLine.navisionDocNo && _.parentETDLine === null);
            }
            const partialChilds = this.etdLinesData.filter(_ => _.parentETDLine !== null && (_.parentETDLine === this.etdLinePartial.id || _.parentETDLine.id === this.etdLinePartial.id));
            this.etdLinePartial.childETDLines = partialChilds;
            // this.etdLinePartialSave = this.copyETDLine(this.etdLinePartial);
            this.etdLinePartialSave = this.etdLinesDataInitSave.find(_ => _._index === this.etdLinePartial._index);
            if(this.etdLinePartialOutstandingQtyOpenModal) {
                this.etdLinesDataInitSave.outstandingQtyConfirmed = this.etdLinePartialOutstandingQtyOpenModal;
            }
            console.log(this.etdLinesDataInitSave);
            console.log(this.etdLinePartialSave);
            console.log(this.etdLinePartial);

            if (partialChilds.length === 0 && this.etdLinePartial.outstandingQty > Utils.getQuantity(this.etdLinePartial.outstandingQtyConfirmed)) {
                this.addEtdLineChildPartial();
            } else {
                this.etdLinePartial.childETDLines = [];
                partialChilds.forEach((item) => {
                    this.etdLinePartial.childETDLines.push(item);
                })
            }

            this.titlePartialModal = this.etdLinePartial.itemReference + ' - ' + Utils.getDateFormatted(this.etdLinePartial.etdDate) + ' - Qty : ' + Utils.getQuantity(this.etdLinePartial.outstandingQty);

            const partialMessage = this.partialMessages.find(_ => _.id === this.etdLinePartial.id);
            if (partialMessage) {
                this.contentMessagePartialModal = partialMessage.message;
            } else {
                this.contentMessagePartialModal = null;
            }
            this.validatePartialModalInputs();
            this.showPartialModal = true;
        },
        cancelPartial() {
            this.etdLinePartial.childETDLines.forEach((child) => {
                this.etdLinesData = this.etdLinesData.filter(_ => _._index !== child._index);
            });
            let index = this.etdLinesData.findIndex(_ => _._index === this.etdLinePartial._index);
            this.etdLinePartialSave.childETDLines.forEach((child) => {
                this.etdLinesData.splice(++index, 0, child);
            });
            this.etdLinePartial.outstandingQtyConfirmed = this.etdLinePartialSave.outstandingQtyConfirmed;
            this.etdLinePartial.shipByConfirmed = this.etdLinePartialSave.shipByConfirmed;
            this.etdLinePartial.etdDateConfirmed = this.etdLinePartialSave.etdDateConfirmed;
            this.forceUpdateETDLinesData(this.etdLinePartial);

            this.etdLinePartialOutstandingQtyOpenModal = null;
            // if(this.etdLinePartial.childETDLines.length > 0) {
            //     this.etdLinePartial.childETDLines.forEach((child) => {
            //         this.etdLinesData = this.etdLinesData.filter(_ => _._index !== child._index);
            //     });
            //     let index = this.etdLinesData.findIndex(_ => _._index === this.etdLinePartial._index);
            //     this.etdLinePartialSave.childETDLines.forEach((child) => {
            //         this.etdLinesData.splice(++index, 0, child);
            //     });
            // } else {
            //     this.etdLinePartial.outstandingQtyConfirmed = this.etdLinesDataInitSave.find(_ => _._index === this.etdLinePartial._index).outstandingQtyConfirmed;
            //     this.forceUpdateETDLinesData(this.etdLinePartial);
            // }
        },
        removeEtdLineChildPartial(child) {
            this.etdLinePartial.childETDLines = this.etdLinePartial.childETDLines.filter(_ => _._index !== child._index);
            this.etdLinesData = this.etdLinesData.filter(_ => _._index !== child._index);
            this.validatePartialModalInputs();
        },
        addEtdLineChildPartial() {
            const copyChild = this.createETDLineChild(this.etdLinePartial);
            const indexNewChild = this.etdLinesData.findIndex(_ => _._index === this.etdLinePartial._index) + this.etdLinePartial.childETDLines.length + 1;
            this.etdLinePartial.childETDLines.push(copyChild);
            this.etdLinesData.splice(indexNewChild, 0, copyChild);
            this.validatePartialModalInputs();
            return copyChild;
        },
        applyUpdateETDPartials() {
            const isPartial = this.etdLinePartial.childETDLines.length > 0;
            this.etdLinePartial.etdLineTag.partial = isPartial;
            this.updateETDDateConfirmed(this.etdLinePartial);
            this.updateOutstandingQtyConfirmed(this.etdLinePartial, this.etdLinePartial.outstandingQtyConfirmed);

            this.etdLinePartial.childETDLines.forEach((child) => {
                child.etdLineTag.partial = isPartial;
                this.updateETDDateConfirmed(child, false);
                this.updateOutstandingQtyConfirmed(child, false);
                this.forceUpdateETDLinesData(child);
            });
            this.forceUpdateETDLinesData(this.etdLinePartial);
            if (this.contentMessagePartialModal !== null) {
                const partialMessage = this.partialMessages.find(_ => _.id === this.etdLinePartial.id);
                if (partialMessage) {
                    partialMessage.message = this.contentMessagePartialModal;
                } else {
                    this.partialMessages.push({id: this.etdLinePartial.id, message: this.contentMessagePartialModal});
                }
                this.contentMessagePartialModal = null;
            }
            this.etdLinePartial = null;
            this.countETDLines = this.etdLinesData.length;
            this.showPartialModal = false;
            this.etdLinePartialOutstandingQtyOpenModal = null;
        },
        checkOutstandingQtyConfirmed(etdLine, event) {
            if(etdLine.parentETDLine === null) {
                this.etdLinePartialOutstandingQtyOpenModal = etdLine.outstandingQtyConfirmed;
            }
            etdLine.outstandingQtyConfirmed = event.target.value;
            this.openPartialModal(etdLine);
            // if (etdLine.childETDLines.length > 0 || this.doesQuantityNeedCreatePartial(etdLine)) {
            //     this.openPartialModal(etdLine);
            // } else {
            //     this.updateOutstandingQtyConfirmed(etdLine);
            // }
        },
        validatePartialModalInputs() {
            if (this.etdLinePartial !== null) {
                this.updatePartialCheckUnicityLineConfirmed();
                this.updatePartialOutstandingQtyConfirmed();
            }
        },
        copyETDLine(etdLine) {
            let copyETDLine = Object.assign({}, etdLine);
            copyETDLine.childETDLines = [];
            etdLine.childETDLines.forEach((item) => {
                copyETDLine.childETDLines.push(Object.assign({}, item))
            });
            copyETDLine.etdLineTag = Object.assign({}, etdLine.etdLineTag);
            copyETDLine.conversation = Object.assign({}, etdLine.conversation);
            return copyETDLine;
        },
        calculatePartialClosedQty(etdLine) {
            let totalQtyConfirmed = parseInt(etdLine.outstandingQtyConfirmed);
            if (etdLine.childETDLines.length > 0) {
                etdLine.childETDLines.forEach((child) => {
                    if (Utils.getDateFormatted(child.etdDateConfirmed)) {
                        totalQtyConfirmed += parseInt(child.outstandingQtyConfirmed);
                    }
                });
            }
            return parseInt(etdLine.outstandingQty) > totalQtyConfirmed ? parseInt(etdLine.outstandingQty) - totalQtyConfirmed : 1;
        },
        updatePartialShipByConfirmed(etdLine) {
            this.updateShipByConfirmed(etdLine);
            this.updatePartialCheckUnicityLineConfirmed();
        },
        updatePartialETDDateConfirmed() {
            this.updatePartialCheckUnicityLineConfirmed();
            this.updatePartialOutstandingQtyConfirmed();
        },
        isPartialETDDisable(etdLine) {
            return Utils.getDateFormatted(etdLine.etdDateConfirmed) === null && parseInt(etdLine.outstandingQtyConfirmed) < this.qtyModifiedToCreatePartial;
        },
        updatePartialCheckUnicityLineConfirmed() {
            const alreadySeen = [];
            let isDuplicatedETD = false;
            this.isETDLineTwoClosed = false;
            alreadySeen.push({
                date: Utils.getDateFormatted(this.etdLinePartial.etdDateConfirmed),
                shipBy: this.etdLinePartial.shipByConfirmed
            });
            this.etdLinePartial.childETDLines.forEach(child => {
                const row = {date: Utils.getDateFormatted(child.etdDateConfirmed), shipBy: child.shipByConfirmed};
                if (row.date === null) {
                    alreadySeen.find(_ => _.date === row.date) !== undefined ? this.isETDLineTwoClosed = true : alreadySeen.push(row);
                } else {
                    alreadySeen.find(_ => _.date === row.date && _.shipBy === row.shipBy) !== undefined ? isDuplicatedETD = true : alreadySeen.push(row);
                }
            });
            this.isETDLinePartialDuplicated = isDuplicatedETD;
        },
        isApplyPartialDisabled() {
            return (this.isETDLinePartialQtyTooMuch || this.isETDLinePartialDuplicated || this.isETDLinePartialQtyTooLow || this.isAtLeastOnePartialQtyTooLow);
        },
        isETDPartialQtyTooLow(etdLine) {
            return parseInt(etdLine.outstandingQtyConfirmed) < this.qtyModifiedToCreatePartial && parseInt(etdLine.outstandingQty) >= this.qtyModifiedToCreatePartial;
        },
        updatePartialOutstandingQtyConfirmed() {
            let total = parseInt(this.etdLinePartial.outstandingQtyConfirmed);
            this.isAtLeastOnePartialQtyTooLow = this.isETDPartialQtyTooLow(this.etdLinePartial);

            let closedPartial = null;
            this.etdLinePartial.childETDLines.forEach((item) => {
                if (this.isETDPartialQtyTooLow(item) && Utils.getDateFormatted(item.etdDateConfirmed) !== null) {
                    this.isAtLeastOnePartialQtyTooLow = true;
                }
                if (Utils.getDateFormatted(item.etdDateConfirmed) === null) {
                    closedPartial = item;
                } else {
                    total += parseInt(item.outstandingQtyConfirmed);
                }
            });

            const qtyLeft = this.etdLinePartial.outstandingQty - total;
            if (qtyLeft > 0) {
                if (!closedPartial) {
                    closedPartial = this.addEtdLineChildPartial();
                }
                closedPartial.outstandingQtyConfirmed = this.calculatePartialClosedQty(this.etdLinePartial);
                total += this.calculatePartialClosedQty(this.etdLinePartial);
            } else {
                if (closedPartial) {
                    this.removeEtdLineChildPartial(closedPartial);
                }
            }

            this.isETDLinePartialQtyTooLow = this.isQuantityTooLow(this.etdLinePartial, total);
            this.isETDLinePartialQtyTooMuch = this.isQuantityTooHigh(this.etdLinePartial, total);
        },
        // doesQuantityNeedCreatePartial(etdLine, outstandingQty = null) {
        //     if (outstandingQty !== null) {
        //         return this.isQuantityTooLow(etdLine, outstandingQty) || this.isQuantityTooHigh(etdLine, outstandingQty);
        //     }
        //     return this.isQuantityTooLow(etdLine, etdLine.outstandingQtyConfirmed) || this.isQuantityTooHigh(etdLine, etdLine.outstandingQtyConfirmed);
        // },
        isQuantityTooLow(etdLine, total) {
            if (etdLine.storeCode === 'OPAL') {
                return parseFloat(total) < (parseFloat(etdLine.outstandingQty) - this.qtyModifiedToCreatePartial);
            }
            // return parseFloat(total) < (parseFloat(etdLine.outstandingQty));
            return false;
        },
        isQuantityTooHigh(etdLine, total) {
            if (etdLine.storeCode === 'OPAL') {
                return parseFloat(total) > (parseFloat(etdLine.outstandingQty) + this.qtyModifiedToCreatePartial);
            }
            // return parseFloat(total) > (parseFloat(etdLine.outstandingQty));
            return false;
        },
        //endregion

        //region Approved and rejected
        approveSelected() {
            this.etdLinesSelected.forEach((item) => {
                this.approveETDLine(item);
            })
        },
        approveETDLine(etdLine) {
            if (etdLine.parentETDLine !== null) {
                let child = this.etdLinesData.find(_ => _.id === etdLine.parentETDLine).childETDLines.find(_ => _._index === etdLine._index);
                child.status = ETDLineStatus.Approved;
            }
            etdLine.status = ETDLineStatus.Approved;
            this.forceUpdateETDLinesData(etdLine);
        },
        rejectSelected() {
            this.etdLinesSelected.forEach((item) => {
                this.rejectETDLine(item);
            })
        },
        rejectETDLine(etdLine) {
            if (etdLine.parentETDLine !== null) {
                let child = this.etdLinesData.find(_ => _.id === etdLine.parentETDLine).childETDLines.find(_ => _._index === etdLine._index);
                child.status = ETDLineStatus.Rejected;
            }
            etdLine.status = ETDLineStatus.Rejected;
            this.forceUpdateETDLinesData(etdLine);
        },
        approveCompleteETDLine(item) {
            if (this.isUserPurchaser() && item.status === ETDLineStatus.WaitingForApproval && item.etdLineTag.completed && !item.etdLineTag.partial) {
                item.status = ETDLineStatus.Approved;
            }
        },
        //endregion

        //region Update tags
        formatETDLinesForUpdating() {
            let onlyParentETDLine = this.etdLinesData.filter(_ => _.parentETDLine === null);
            onlyParentETDLine.forEach((item) => {
                if (item.etdDateConfirmed === '') {
                    item.etdDateConfirmed = null;
                }
                item.childETDLines.forEach((child) => {
                    child.parentETDLine = null;
                    if (child.etdDateConfirmed === '') {
                        child.etdDateConfirmed = null;
                    }
                })
            });
            return onlyParentETDLine;
        },
        handleMultipleETDDateUpdate() {
            this.etdLinesSelected.forEach((item, index) => {
                item.etdDateConfirmed = this.dateSelectedETDDateModal;
                this.updateETDDateConfirmed(item);
            })
            this.dateSelectedETDDateModal = null;
        },
        handleMultipleShipByUpdate() {
            this.etdLinesSelected.forEach((item) => {
                item.shipByConfirmed = this.shipBySelectedShipByModal;
                this.updateShipByConfirmed(item);
            })
            this.shipBySelectedShipByModal = null;
        },
        updateETDDateConfirmed(etdLine) {
            // etdLine.etdDateConfirmed = etdDate;
            if (etdLine.etdDateConfirmed === null || etdLine.etdDateConfirmed === '') {
                etdLine.etdLineTag.etdChanged = false;
                etdLine.etdLineTag.closed = true;
            } else {
                if (etdLine.parentETDLine === null) {
                    etdLine.etdLineTag.etdChanged = Utils.getDateFormatted(etdLine.etdDate) !== Utils.getDateFormatted(etdLine.etdDateConfirmed);
                } else {
                    etdLine.etdLineTag.etdChanged = false; // Remove tag for childs
                }
                etdLine.etdLineTag.closed = false;
            }
            this.updateCompleteTag(etdLine.etdLineTag);
            this.updateETDLineStatus(etdLine);
            this.forceUpdateETDLinesData(etdLine);
        },
        updateShipByConfirmed(etdLine) {
            etdLine.etdLineTag.shipByChanged = etdLine.shipBy !== etdLine.shipByConfirmed;
            this.updateCompleteTag(etdLine.etdLineTag);
            this.updateETDLineStatus(etdLine);
            this.forceUpdateETDLinesData(etdLine);
        },
        forceUpdateETDLinesData(element) {
            const ind = this.etdLinesData.findIndex(_ => _._index === element._index);
            this.etdLinesData.splice(ind, 1);
            this.etdLinesData.splice(ind, 0, element);
        },
        updateOutstandingQtyConfirmed(etdLine, forceUpdate = true) {
            if (etdLine.etdLineTag.partial) {
                etdLine.etdLineTag.qtyChanged = false;
            } else {
                etdLine.etdLineTag.qtyChanged = parseFloat(etdLine.outstandingQty) > parseFloat(etdLine.outstandingQtyConfirmed);
            }
            this.updateCompleteTag(etdLine.etdLineTag);
            this.updateETDLineStatus(etdLine);
            if (forceUpdate) {
                this.forceUpdateETDLinesData(etdLine);
            }
        },
        updateETDLineStatus(etdLine) {
            const etdLineSave = this.etdLinesDataInitSave.find(_ => _._index === etdLine._index);
            switch (this.user.type) {
                case UserType.Vendor:
                    if (etdLineSave) {
                        if (etdLineSave.outstandingQtyConfirmed !== etdLine.outstandingQtyConfirmed ||
                            etdLineSave.etdDateConfirmed !== etdLine.etdDateConfirmed ||
                            etdLineSave.shipByConfirmed !== etdLine.shipByConfirmed) {
                            etdLine.status = ETDLineStatus.WaitingForApproval;
                        } else {
                            etdLine.status = etdLineSave.status;
                        }
                    }
                    break;
                case UserType.Purchaser:
                    if (etdLineSave) {
                        if (etdLineSave.outstandingQtyConfirmed !== etdLine.outstandingQtyConfirmed ||
                            etdLineSave.etdDateConfirmed !== etdLine.etdDateConfirmed ||
                            etdLineSave.shipByConfirmed !== etdLine.shipByConfirmed) {
                            etdLine.status = ETDLineStatus.Rejected;
                        } else {
                            etdLine.status = etdLineSave.status;
                        }
                    }
                    break;
            }
        },
        updateCompleteTag(etdLineTag) {
            etdLineTag.completed = !(etdLineTag.qtyChanged || etdLineTag.shipByChanged || etdLineTag.etdChanged || etdLineTag.closed || etdLineTag.partial);
        },
        openETDLineConversationModal(etdLine) {
            this.etdLineSelected = etdLine;
            this.titleConversationModal = etdLine.navisionDocNo + ' ' + etdLine.itemReference + ' - Summary';
            this.showETDLineConversationModal = true;
        },
        openETDConversationModal() {
            this.titleConversationModal = this.vendorNo + ' ' + Utils.getDateFormatted(this.etd.etdDate) + ' - Summary';
            this.showETDConversationModal = true;
        },
        //endregion

        //region Rights and displays
        isETDLineApprovable(etdLine) {
            return this.isRowEditable(etdLine) && etdLine.status !== ETDLineStatus.Approved;
        },
        isETDLineRejectable(etdLine) {
            return this.isRowEditable(etdLine) && etdLine.status !== ETDLineStatus.Rejected;
        },
        isPartialAvailable(etdLine) {
            return this.isRowEditable(etdLine) && etdLine.etdLineTag.partial;
        },
        isRowEditable(etdLine) {
            return this.isUserEnabledToUpdate() && !etdLine.readOnly && !(this.isUserVendor() && etdLine.status === ETDLineStatus.Approved);
        },
        isDateNull(date) {
            return Utils.getDateFormatted(date) === null;
        },
        isETDDateNullable(etdLine) {
            return etdLine.parentETDLine !== null;
        },
        isUserVendor() {
            return this.user.type === UserType.Vendor;
        },
        isUserPurchaser() {
            return this.user.type === UserType.Purchaser;
        },
        isETDClosed() {
            return this.etd.closed;
        },
        isUserEnabledToUpdate() {
            if (this.isReadOnly) {
                return false;
            }
            if (this.isETDClosed()) {
                return false;
            }
            if ((this.isUserPurchaser() && this.etd.status === ETDStatus.WaitingPurchaser) ||
                (this.isUserVendor() && this.etd.status === ETDStatus.WaitingVendor)) {
                return true;
            }
            return false;
        },
        hideReadOnlyLine() {
            this.areETDLineReadOnlyHidden = true;
        },
        showReadOnlyLine() {
            this.areETDLineReadOnlyHidden = false;
        },
        //endregion

        //region Selection rows
        changeETDLineChecked(etdLine, value, isValueAlreadyChanged = false) {
            if (!isValueAlreadyChanged) {
                etdLine.checked = value;
            }

            const element = this.$refs['input-checked-' + etdLine._index];
            if (element) {
                const parent = element.closest(".item-line");
                if (etdLine.checked) {
                    parent.classList.add("selected");
                } else {
                    parent.classList.remove("selected");
                }
            }

            if (etdLine.checked) {
                if (!this.etdLinesSelected.find(_ => _._index === etdLine._index)) {
                    this.etdLinesSelected.push(etdLine);
                }
            } else {
                this.etdLinesSelected = this.etdLinesSelected.filter(_ => _._index !== etdLine._index);
            }
            this.forceUpdateETDLinesData(etdLine);
        },
        selectAllRows() {
            this.getETDLinesFiltered().forEach((item) => {
                this.changeETDLineChecked(item, true);
            });
        },
        unselectAllRows() {
            this.getETDLinesFiltered().forEach((item) => {
                this.changeETDLineChecked(item, false);
            })
        },
        areAllRowsSelected() {
            this.isAllChecked = this.etdLinesSelected.length === this.countETDLinesDisplayed;
            return this.isAllChecked;
        },
        //endregion

        //region Cancel updates
        returnPreviousPage() {
            let currentUrl = window.location.href;
            let tmpUrl = currentUrl.split('/');
            let previousePage = this.previousePage.split('/');
            if (tmpUrl[2] !== previousePage[2]) {
                return window.location.href = "/";
            }
            window.location.href = this.previousePage;
            
        },
        validateCancelChanges() {
            this.cleanLocalStorage();
            this.returnPreviousPage();
        },
        cleanLocalStorage() {
            localStorage.removeItem(this.getLocalStorageETDLinesDataItemName());
            localStorage.removeItem(this.getLocalStorageetdLinesDataInitSaveItemName());
        },
        cancelChanges() {
            if (!this.isAtLeastOneLineChanged()) {
                this.validateCancelChanges();
            } else {
                this.showCancelUpdateModal = true;
            }
        },
        //endregion

        createUniqueIndex() {
            const array = new Uint32Array(25);
            return new Date().getUTCMilliseconds() + '_' + window.crypto.getRandomValues(array)[0];
        },
        getETDLineCountMessage(etdLine) {
            const t = this.conversationsCount.filter(_ => _.id === etdLine.id);
            if (t.length > 0) {
                return t[0].countMessages;
            }
            return 0;
        },
        formatETDLine(item) {
            item._index = this.createUniqueIndex();
            item.checked = false;
            item.outstandingQtyConfirmed = Utils.getQuantity(item.outstandingQtyConfirmed);
            item.outstandingQty = Utils.getQuantity(item.outstandingQty);
            item.etdDateConfirmed = Utils.getDateFormatted(item.etdDateConfirmed);
            item.countMessages = this.getETDLineCountMessage(item);
        },
        initTable() {
            const etdLinesStorage = localStorage.getItem(this.getLocalStorageETDLinesDataItemName());
            const etdLinesSaveStorage = localStorage.getItem(this.getLocalStorageetdLinesDataInitSaveItemName());
            // const etdLinesStorage = null;
            // const etdLinesSaveStorage = null;
            if (this.isUserEnabledToUpdate() && etdLinesStorage && etdLinesSaveStorage) {
                this.etdLinesData = JSON.parse(etdLinesStorage);
                this.etdLinesDataInitSave = JSON.parse(etdLinesSaveStorage);
            } else {
                this.etdLines.forEach((item, index) => {
                    if (item.parentETDLine === undefined) {
                        item.parentETDLine = null;
                    }
                    this.formatETDLine(item);
                    if (item.childETDLines !== null && item.childETDLines.length > 0) {
                        item.childETDLines.forEach((child) => {
                            this.formatETDLine(child);
                        });
                    }
                });

                this.etdLinesData = this.etdLines.map(object => ({...object}));
                let cpt = 1;
                this.etdLines.forEach((item, index) => {
                    if (item.childETDLines !== null && item.childETDLines.length > 0) {
                        item.childETDLines.forEach((child) => {
                            this.etdLinesData.splice(index + cpt, 0, child);
                            cpt++;
                        });
                    }
                });
                this.etdLinesData.forEach((item) => {
                    this.etdLinesDataInitSave.push(this.copyETDLine(item));
                    // Apply modification after save to display updates at the validation
                    this.approveCompleteETDLine(item);
                });
                localStorage.setItem(this.getLocalStorageetdLinesDataInitSaveItemName(), JSON.stringify(this.etdLinesDataInitSave));
            }

            this.countETDLines = this.etdLinesData.length;

            switch (this.user.type) {
                case UserType.Vendor:
                    this.fields = [
                        {prop: '_action', name: ' ', actionName: "actionsSlot", width: 70},
                        {prop: '_action', name: ' ', actionName: "checkedSlot", width: 30},
                        {prop: '_action', name: 'Stat.', actionName: "statusSlot", width: 40},
                        {prop: 'navisionDocNo', name: 'PO N°', sortable: true},
                        {prop: 'piNo', name: 'PI N°', sortable: true, class: 'text-primary'},
                        {prop: 'itemReference', name: 'Item Ref.', sortable: true},
                        {
                            prop: '_action',
                            name: 'O. Qty',
                            sortable: true,
                            class: 'is-numeric',
                            actionName: 'outstandingQtySlot'
                        },
                        {prop: '_action', name: 'Delivery Date', sortable: true, actionName: 'deliveryDateSlot'},
                        {prop: 'shipBy', name: 'Ship By', sortable: true},
                        {prop: '_action', name: 'Tags', actionName: 'etdLineTagsSlot'},
                        {
                            prop: '_action',
                            name: 'Qty Confirmed',
                            actionName: 'outstandingQtyConfirmedSlot',
                            width: 82
                        },
                        {prop: '_action', name: 'ETD Confirmed', actionName: 'etdDateConfirmedSlot', width: 140},
                        {prop: '_action', name: 'Ship Confirmed', actionName: 'shipByConfirmedSlot', width: 90},
                        {prop: 'orderType', name: 'Order Type', sortable: true, class: 'is-numeric'},
                    ];
                    break;
                case UserType.Purchaser:
                    this.fields = [
                        {prop: '_action', name: ' ', actionName: "actionsSlot", width: 70},
                        {prop: '_action', name: ' ', actionName: "checkedSlot", width: 30},
                        {prop: '_action', name: 'Stat.', actionName: "statusSlot", width: 40},
                        {prop: 'navisionDocNo', name: 'PO N°', sortable: true},
                        {prop: 'piNo', name: 'PI N°', sortable: true, class: 'text-primary'},
                        {prop: 'itemReference', name: 'Item Ref.', sortable: true},
                        {
                            prop: '_action',
                            name: 'O. Qty',
                            sortable: true,
                            eClass: {'is-numeric': 'true'},
                            actionName: 'outstandingQtySlot'
                        },
                        {prop: '_action', name: 'Deliv. Date', sortable: true, actionName: 'deliveryDateSlot'},
                        {prop: 'shipBy', name: 'Ship By'},
                        {prop: '_action', name: 'Tags', actionName: 'etdLineTagsSlot'},
                        {
                            prop: '_action',
                            name: 'Qty Confirmed',
                            actionName: 'outstandingQtyConfirmedSlot',
                            width: 82
                        },
                        {prop: '_action', name: 'ETD Confirmed', actionName: 'etdDateConfirmedSlot', width: 140},
                        {prop: '_action', name: 'Ship Confirmed', actionName: 'shipByConfirmedSlot', width: 90},
                        {prop: 'orderType', name: 'Order Type', sortable: true, eClass: {'is-numeric': 'true'},},
                        {prop: 'storeCode', name: 'Loc. Code', sortable: true},
                        {
                            prop: '_action',
                            name: 'Qty. Apr. Rgt',
                            actionName: 'availableQtySlot',
                            eClass: {'is-numeric': 'true'},
                            width: 30
                        },
                        {prop: 'yourReference', name: 'Your Ref.', sortable: true},
                        {prop: '_action', name: 'Com.', actionName: 'commentsSlot', width: 30},
                    ];
                    break;
            }
        },
        getETDLinesFiltered() {
            let tmpItems = this.filter.filterRow !== '' ? this.etdLinesData.filter(_ => _.piNo.toLowerCase().includes(this.filter.filterRow.toLowerCase()) || _.itemReference.toLowerCase().includes(this.filter.filterRow.toLowerCase()) || _.navisionDocNo.toLowerCase().includes(this.filter.filterRow.toLowerCase())) : this.etdLinesData;

            tmpItems = this.filter.areETDLineReadOnlyHidden ? tmpItems.filter(_ => !_.readOnly) : tmpItems;

            if (this.filter.statusFilterSelected.length > 0) {
                tmpItems = tmpItems.filter(_ => this.filter.statusFilterSelected.includes(_.status));
            }

            if (this.filter.tagsFilterSelected.length > 0) {
                const lambda = _ => (_.etdLineTag.completed && this.filter.tagsFilterSelected.includes(ETDLineTag.Completed)) ||
                    (_.etdLineTag.qtyChanged && this.filter.tagsFilterSelected.includes(ETDLineTag.QtyChanged)) ||
                    (_.etdLineTag.shipByChanged && this.filter.tagsFilterSelected.includes(ETDLineTag.ShipByChanged)) ||
                    (_.etdLineTag.partial && this.filter.tagsFilterSelected.includes(ETDLineTag.Partial)) ||
                    (_.etdLineTag.closed && this.filter.tagsFilterSelected.includes(ETDLineTag.Closed)) ||
                    (_.etdLineTag.etdChanged && this.filter.tagsFilterSelected.includes(ETDLineTag.ETDChanged));
                tmpItems = tmpItems.filter(lambda);
            }

            this.countETDLinesDisplayed = tmpItems.length;

            if(this.etdLinesData.length > 0) {
                localStorage.setItem(this.getLocalStorageETDLinesDataItemName(), JSON.stringify(this.etdLinesData));
            }
            return tmpItems;
        },
        getLocalStorageETDLinesDataItemName() {
            return 'etdLines_' + this.etd.etdDate + '_' + this.vendorNo;
        },
        getLocalStorageetdLinesDataInitSaveItemName() {
            return 'etdLinessave_' + this.etd.etdDate + '_' + this.vendorNo;
        },
        createETDLineChild(etdLineParent) {
            let copyChild = this.copyETDLine(etdLineParent);
            copyChild.parentETDLine = Object.assign({}, etdLineParent);
            copyChild.parentETDLine.childETDLines = [];
            copyChild.childETDLines = [];
            copyChild.countMessages = 0;
            copyChild.id = 0; // Needed in back to normalize object to ETDLine
            copyChild._index = this.createUniqueIndex();
            copyChild.checked = false;
            copyChild.outstandingQtyConfirmed = this.calculatePartialClosedQty(etdLineParent);
            copyChild.originalLineOutstandingQty = etdLineParent.outstandingQty;
            copyChild.etdDateConfirmed = null;
            copyChild.conversation = null;
            copyChild.navisionLineNo = 0;

            switch (this.user.type) {
                case UserType.Purchaser:
                    copyChild.status = ETDLineStatus.Initial;
                    break;
                case UserType.Vendor:
                    copyChild.status = ETDLineStatus.WaitingForApproval;
                    break;
            }

            return copyChild;
        }
    }
}
</script>

<style lang="scss" scoped>
@import "../../styles/app.scss";


</style>