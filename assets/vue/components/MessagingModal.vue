<template>
    <div>
        <div class="body-modal-conversation">
            <template v-if="isLoadingConversation">
                <div class="d-flex justify-content-center my-3">
                    <b-spinner label="Loading..."></b-spinner>
                </div>
            </template>
            <template v-else>
                <template v-if="messages.length === 0">
                    <p>No message</p>
                </template>
                <template v-else-if="messages.length > 0">
                    <div v-for="message in messages" class="block-message">
                        <div class="message-date">{{ message.dateAdd | fromFormatDate }} <span>{{ message.dateAdd | formatDateTime }}</span></div>
                        <div :class="user.code === message.writeByUser.code ? 'owner' : ''" class="wrapper">
                            <div class="message">
                                <div class="message--header">
                                    <div class="tags-list my-1">
                                  <span class="badge badge-status-etdline-tag-valid" v-show="message.countApprovedChanged !== null && message.countApprovedChanged > 0">
                                    Approved : {{ message.countApprovedChanged }}</span>
                                        <span class="badge badge-status-etdline-tag-important" v-show="message.countRejectedChanged !== null && message.countRejectedChanged > 0">
                                    Rejected : {{ message.countRejectedChanged }}</span>
                                        <span class="badge badge-status-etdline-tag-modified" v-show="message.countValidateShipByChanged !== null && message.countValidateShipByChanged > 0">
                                    Ship By Changed : {{
                                                message.countValidateShipByChanged
                                            }}</span>
                                        <span class="badge badge-status-etdline-tag-modified" v-show="message.countValidateETDDateChanged !== null && message.countValidateETDDateChanged > 0">
                                    ETD Date Changed : {{
                                                message.countValidateETDDateChanged
                                            }}</span>
                                        <span class="badge badge-status-etdline-tag-modified" v-show="message.countValidateQtyChanged !== null && message.countValidateQtyChanged > 0">
                                    Qty Changed : {{ message.countValidateQtyChanged }}</span>
                                    </div>
                                    <div class="message--infos">
                                        <span class="message--infos--code">{{ message.writeByUser.code }}</span>
                                    </div>
                                </div>
                                <p v-if="message.content !== null" class="message--content">{{ message.content }}</p>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </div>
        <div v-if="!this.isReadOnly" class="footer-modal-conversation w-100">
            <b-input-group>
                <b-form-textarea
                    id="textarea"
                    placeholder="Enter your text here..."
                    v-model="newMessageContent"
                    rows="3"
                    max-rows="6"
                    @keydown.enter.ctrl="addConversationMessage()"
                ></b-form-textarea>
                <b-input-group-append>
                    <b-button
                        variant="primary"
                        @click="addConversationMessage()"
                    >
                        Send<br />(ctrl + enter)
                    </b-button>
                </b-input-group-append>
            </b-input-group>
        </div>
    </div>
</template>


<script>
import * as API from "./../utils/api";
import {addConversationMessage} from "./../utils/api";


export default {
    name: "MessagingModal",
    props: {
        etd: {type: Object, required: true},
        etdLine: {type: Object, required: false},
        isReadOnly: {type: Boolean, required: true},
        user: {type: Object, required: true}
    },
    data() {
        return {
            messages: [],
            newMessageContent: null,
            isLoadingConversation: false,
        }
    },
    mounted() {
        this.getConversation();
    },
    methods: {
        getConversation() {
            this.isLoadingConversation = true;
            const api = this.etdLine === undefined ? API.getConversation(this.etd.id, null) : API.getConversation(this.etd.id, this.etdLine.id);
            api.then((response) => {
                this.messages = response.data;
            })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.isLoadingConversation = false;
                });
        },
        addConversationMessage() {
            const api = this.etdLine === undefined ? API.addConversationMessage(this.etd.id, null, this.newMessageContent) : API.addConversationMessage(this.etd.id, this.etdLine.id, this.newMessageContent);
            api.then((response) => {
                this.getConversation();
                this.newMessageContent = null;
            })
                .catch((error) => {
                    console.log(error);
                });
        },
    }
}
</script>

<style lang="scss" scoped>
@import "../../styles/app.scss";

.body-modal-conversation {
    max-height: calc(100vh - 260px);
    overflow-y: scroll;

    .block-message {
        margin: 15px 0;
        .message-date {
            font-size: 13px;
            text-align: center;
            span {
                margin-left: 15px;
                font-style: italic;
                color: #B9BCBF;
            }
        }
    }
    .wrapper {
        display: flex;
        width: 100%;
        justify-content: flex-start;
        padding: 0 1rem;
        .message {
            max-width: 80%;
            width: auto;
            height: auto;
            position: relative;
            //margin: 1rem 0;
            .tags-list{
              display: flex;
              justify-content: flex-start;
            }
            &--header {
                display: flex;
                align-items: center;
            }
            &--content {
                white-space: pre-line;
                font-size: 16px;
                line-height: 14px;
                background-color: $lightgrey;
                padding: 1rem;
                border-radius: 4px;
                margin-bottom: 5px;
            }

            &--infos {
                text-align: left;
                color: $darkgrey;
                font-size: 14px;
                margin-left: 15px;
                &--code {
                    font-weight: bold;
                }
            }
        }

        &.owner {
            justify-content: flex-end;

            .message {
                &--header {
                    justify-content: flex-end;
                    align-items: center;
                }
                &--content {
                    background-color: $main-color-light;
                    text-align: right;
                }

                &--infos {
                  text-align: right;
                }
            }
        }
    }
}

.footer-modal-conversation {
    padding-top: 1rem;
}
</style>