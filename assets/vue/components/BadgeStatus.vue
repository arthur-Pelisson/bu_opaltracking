<template>
    <div class="badge" :class="this.class" v-b-tooltip.hover :title="content" v-html="this.icon"></div>
</template>

<script>
import {BadgeStatusType, ETDLineStatus, ETDLineTag, ETDStatus} from "../utils/enum";

export default {
    name: "BadgeStatus",
    props: {
        content: {type: String, required: true},
        typeBadge: {type: String, required: true},
    },
    data() {
        return {
            icon: '',
            class: '',
        }
    },
    methods: {
        setETDStatus() {
            switch (this.content) {
                case ETDStatus.WaitingVendor:
                    this.class = 'badge-status-waiting-vendor'
                    this.icon = '<i class="fas fa-pause"></i>';
                    break;
                case ETDStatus.WaitingPurchaser:
                    this.class = 'badge-status-waiting-purchaser'
                    this.icon = '<i class="fas fa-pause"></i>';
                    break;
                case ETDStatus.Closed:
                    this.class = 'badge-status-closed'
                    this.icon = '<i class="fas fa-times"></i>';
                    break;
            }
        },
        setETDLineStatus() {
            switch (this.content) {
                case ETDLineStatus.Approved:
                    this.class = 'badge-status-etdline-approved';
                    this.icon = '<i class="fas fa-check"></i>';
                    break;
                case ETDLineStatus.Rejected:
                    this.class = 'badge-status-etdline-rejected';
                    this.icon = '<i class="fas fa-times"></i>';
                    break;
                case ETDLineStatus.Initial:
                    this.class = 'badge-status-etdline-initial';
                    this.icon = '<i class="fas fa-pause"></i>';
                    break;
                case ETDLineStatus.WaitingForApproval:
                    this.class = 'badge-status-etdline-waiting-approval';
                    this.icon = '<i class="fas fa-pause"></i>';
                    break;
            }
        },
        setETDLineTagStatus() {
            switch (this.content) {
                case ETDLineTag.Completed:
                    this.class = 'badge-status-etdline-tag-valid';
                    this.icon = '<i class="fas fa-check"></i>';
                    break;
                case ETDLineTag.Closed:
                    this.class = 'badge-status-etdline-tag-important';
                    this.icon = '<i class="fas fa-times"></i>';
                    break;
                case ETDLineTag.Partial:
                    this.class = 'badge-status-etdline-tag-important';
                    this.icon = '<i class="fas fa-exclamation"></i>';
                    break;
                case ETDLineTag.ETDChanged:
                    this.class = 'badge-status-etdline-tag-modified';
                    this.icon = '<i class="fas fa-calendar"></i>';
                    break;
                case ETDLineTag.ShipByChanged:
                    this.class = 'badge-status-etdline-tag-modified';
                    this.icon = '<i class="fas fa-plane"></i>';
                    break;
                case ETDLineTag.QtyChanged:
                    this.class = 'badge-status-etdline-tag-modified';
                    this.icon = '<i class="fas fa-pen"></i>';
                    break;
            }
        },
        updateBadge() {
            switch (this.typeBadge) {
                case BadgeStatusType.ETDStatus:
                    this.setETDStatus();
                    break;
                case BadgeStatusType.ETDLineStatus:
                    this.setETDLineStatus();
                    break;
                case BadgeStatusType.ETDLineTag:
                    this.setETDLineTagStatus();
                    break;
            }
        }
    },
    mounted() {
        this.updateBadge();
    },
    updated() {
        this.updateBadge();
    }
}
</script>

<style lang="scss" scoped>
@import "../../styles/app.scss";

</style>