<script setup>
  import {
    alphaDashValidator,
    alphaValidator,
    betweenValidator,
    confirmedValidator,
    emailValidator,
    integerValidator,
    lengthValidator,
    passwordValidator,
    regexValidator,
    requiredValidator,
    urlValidator,
  } from '@validators'
  const refForm = ref()
</script>

<template>
<VForm
  ref="refForm"
  @submit.prevent
>
    <VRow>
    <!-- SECTION User Details -->
    <VCol cols="12">
      <VCard>
        <VCardText class="text-center pt-15">
          <!-- 👉 Avatar -->
          <VAvatar

            :size="200"
          >
            <VImg
              v-if="true"
              :src="isEdit ? body.photo : data.photo"
              @click="changeImage()"
            />
            <span
              v-else
              class="text-5xl font-weight-semibold"
            >
              {{ avatarText(data.name) }}
            </span>
          </VAvatar>

          <!-- 👉 User fullName -->
          <h6 class="text-h6 mt-4">
            {{ data.name }}
          </h6>

          <!-- 👉 Role chip -->
          <!-- :color="resolveUserRoleVariant(data.role_name).color" -->
          <VChip
          label
          size="small"
            class="text-capitalize mt-4"
          >
            {{ data.role_name }}
          </VChip>
        </VCardText>

        <VDivider />

        <!-- 👉 Details -->
        <VCardText>
          <p class="text-sm text-uppercase text-disabled">
            Details
          </p>

        
          <!-- 👉 User Details list -->
            <VList class="card-list mt-2">
              <VListItem>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Name:
                    <VRow v-if="isEdit">
                      <VCol cols="4">
                        <VTextField
                          v-model="body.name"
                          class="mt-1"
                          clearable
                          placeholder="Name"
                          :rules="[requiredValidator]"
                        />
                      </VCol>
                    </VRow>
                    <span class="text-body-2" v-else>
                      {{ data.name }}
                    </span>
                  </h6>
                </VListItemTitle>
              </VListItem>

              <VListItem>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Email:
                    <VRow v-if="isEdit">
                      <VCol cols="4">
                        <VTextField
                          class="mt-1"
                          v-model="body.email"
                          persistent-placeholder
                          clearable
                          placeholder="Must be a valid email"
                          :rules="[requiredValidator, emailValidator]"
                        />
                      </VCol>
                    </VRow>
                    <span class="text-body-2" v-else>
                      {{ data.email }}
                    </span>
                  </h6>
                </VListItemTitle>
              </VListItem>
              <VListItem>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Status:

                    <!-- :color="resolveUserStatusVariant(data.status == 1 ? 'Active' : 'Inactive')" -->
                    <VChip
                      label
                      size="small"
                      class="text-capitalize"
                    >
                      {{ data.status == 1 ? 'Active' : 'Inactive' }}
                    </VChip>
                  </h6>
                </VListItemTitle>
              </VListItem>
              <VListItem>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Role:
                    <span class="text-capitalize text-body-2">{{ data.role_name }}</span>
                  </h6>
                </VListItemTitle>
              </VListItem>
            </VList>
            <input
              type="file"
              ref="fileInput"
              @change="handleFileUpload"
              style="display: none"
            />
        </VCardText>

        <!-- 👉 Edit and Suspend button -->
        <VCardText class="d-flex justify-center">
          <VBtn
            variant="flat"
            class="me-3"
            @click="editForm()"
          >
            {{ isEdit ? 'Cancel' : 'Edit' }}
          </VBtn>
          <VBtn
            variant="flat"
            v-if="isEdit"
            @click="refForm?.validate()"
            type="submit"
          >
            Save
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
    <!-- !SECTION -->
  </VRow>
</VForm>
</template>

<script>
  import api from "@/apis/CommonAPI"
  import { avatarText } from '@core/utils/formatters'
  import Swal from 'sweetalert2'

  export default {
    components: {
    },
    async mounted(){
      await this.doGetById(this.$route.params.id)
    },
    data(){
      return {
        body: {
          name: '',
          email: '',
          status: '',
          roleId: ''
        },
        data: [],
        loading: false,
        infoMessage: '',
        warningMessage: '',
        errorMessage: '',
        isEdit: false
      }
    },
    watch: {
    },
    computed: {
    },
    methods: {
      handleFileUpload(event) {
        const file = event.target.files[0];
        console.log("🚀 ~ handleFileUpload ~ file:", file)
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.body.photo = e.target.result; // Display the selected image
            console.log("🚀 ~ handleFileUpload ~ this.body:", this.body)
          };
          reader.readAsDataURL(file);
        }
      },
      changeImage(){
        if(!this.isEdit){
          return
        }
        this.$refs.fileInput.click();
      },
      editForm(){
        this.body = {...this.data}
        this.isEdit = !this.isEdit
      },
      async doGetById(user_id){
          this.loading = true

          let uri = `/api/v1/users/${user_id}`;
          let responseBody = await api.jsonApi(uri,'GET');
          console.log("🚀 ~ doGetById ~ responseBody:", responseBody)
          if( responseBody.status != 200 ){
            this.infoMessage = '';
            this.warningMessage = '';
            this.errorMessage = responseBody.message;
          }else{
            this.data = responseBody.data
            if(responseBody.data.length<=0){
                this.infoMessage = ''
                this.warningMessage = 'Data not found'
            }else{
                this.infoMessage = ''
                this.warningMessage = ''
            }
            this.errorMessage = ''

          }
          this.loading = false
      },
      async doDelete(user_id){
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#7367F0",
          cancelButtonColor: "#EA5455",
          confirmButtonText: "Yes, delete it!",
          customClass: {
            confirmButton: 'confirm-button-text-white',
            cancelButton: 'confirm-button-text-white'
          }
      }).then(async (result) => {
          if (result.isConfirmed) {
            this.loading = true
            let uri = `/api/v1/users`;
            let responseBody = await api.jsonApi(uri,'DELETE',JSON.stringify({userId: user_id}));
            
            if( responseBody.status != 200 ){
              this.infoMessage = '';
              this.warningMessage = '';
              this.errorMessage = responseBody.message;
            }else{
              Swal.fire('Deleted!', responseBody.message, 'success')
            }
            this.loading = false
            this.doSearch(1)
          }
        })
      },
    },
  }
</script>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 0.7rem;
}

.text-capitalize {
  text-transform: capitalize !important;
}
</style>
