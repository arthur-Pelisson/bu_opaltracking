<template>
    <div>
        <div class="body-modal-conversation">
            <template v-if="isLoadingFiles">
                <div class="d-flex justify-content-center my-3">
                    <b-spinner label="Loading..."></b-spinner>
                </div>
            </template>
            <template v-else>
                <template v-if="files.length === 0">
                    <p>No files</p>
                </template>
                <template v-else-if="files.length > 0">
                    <div class="wrapper" >
                        <div class="file-display" v-for="file in files">
                            <div style="margin-top: 5px">
                                {{ file.name }}
                            </div>
                            <div style="display: flex; align-items: center;">
                                <div class="file-display--date">
                                    {{ file.modificationDate | formatDate }}
                                </div>
                                <span class="btn btn-hover btn-action" v-on:click="downloadFile(file)">
                                    <i class="fas fa-download"></i>
                                </span>
                                    <span class="btn btn-hover btn-action" v-on:click="deleteFile(file)">
                                    <i class="fas fa-times"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="modal-bottom" style="display: flex;">
                    <b-form-file
                        ref="filesToImport"
                        class="input-files"
                        v-model="filesToImport"
                        multiple
                        placeholder="Choose a file or drop it here..."
                        drop-placeholder="Drop file here..."
                        accept=".xlsx, .pdf, .jpg"
                    ></b-form-file>
                    <button style="margin-left: 30px; min-width: 180px" :disabled="this.filesToImport.length === 0" class="btn btn-secondary btn-icon" @click="importFiles()">Import file(s)</button>
                </div>
            </template>
        </div>
        <div class="footer-modal-conversation w-100">
        </div>
    </div>
</template>

<script>
import {BFormFile} from "bootstrap-vue";
import * as API from "../utils/api";
import * as Utils from "./../utils/utils";

export default {
    name: "ETDDocuments",
    components: {
        BFormFile,
    },
    props: {
        etd: {type: Object, required: true},
        user: {type: Object, required: true}
    },
    data() {
        return {
            filesToImport: [],
            isLoadingFiles: false,
            files: [],
        }
    },
    methods: {
        importFiles() {
            this.isLoadingFiles = true;
            API.importETDFiles(this.etd.id, this.filesToImport)
                .then((response) => {
                    this.files = response.data;
                    this.filesToImport = [];
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.isLoadingFiles = false;
                });
        },
        getFiles() {
            this.isLoadingFiles = true;
            API.getETDFiles(this.etd.id)
                .then((response) => {
                    this.files = response.data;
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.isLoadingFiles = false;
                });
        },
        downloadFile(file) {
            API.getETDFile(this.etd.id, file.name)
                .then((response) => {
                    Utils.downloadFile(file.name, response.data);
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        deleteFile(file) {
            this.isLoadingFiles = true;
            API.deleteETDFile(this.etd.id, file.name)
                .then((response) => {
                    this.files = this.files.filter(_ => _ !== file);
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.isLoadingFiles = false;
                });
        }
    },
    mounted() {
        this.getFiles();
    }
}
</script>

<style lang="scss" scoped>
@import "../../styles/app.scss";

.wrapper {
    display: flex;
    width: 100%;
    flex-wrap: wrap;

    .file-display {
        flex: 0 0 33.333333%;
        display: flex;
        justify-content: space-between;
        padding: 0 20px;
        align-items: center;

        &--date {
            margin-top: 5px;
            color: $darkgrey;
            font-style: italic;
            font-size: 0.9em;
        }
    }
}

.modal-bottom {
    margin-top: 20px;
}

</style>