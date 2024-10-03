<script setup>
  const refInputEl = ref()
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="User Details">
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded
            size="150"
            class="me-6"
            :image="body.photo ? body.photo : data.photo"
          />

          <!-- 👉 Upload Photo -->
          <form
            ref="refForm"
            class="d-flex flex-column justify-center gap-4"
          >
            <div class="d-flex flex-wrap gap-2">
              <VBtn
                color="primary"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="tabler-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg"
                hidden
                @input="handleFileUpload($event)"
              >

              <VBtn
                type="reset"
                color="secondary"
                variant="tonal"
                @click="resetForm"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon
                  icon="tabler-refresh"
                  class="d-sm-none"
                />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, JPEG or PNG. Max size of 1MB
            </p>
          </form>
        </VCardText>

        <VDivider />

        <VCardText class="pt-2">
          <!-- 👉 Form -->
          <VForm @submit.prevent="doUpdate" v-model="isFormValid" class="mt-6">
            <VRow>
              <!-- 👉 First Name -->
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="body.nip"
                  label="NIP"
                  readonly
                />
              </VCol>

              <!-- 👉 Last Name -->
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="body.name"
                  label="Nama"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="body.email"
                  label="E-mail"
                  type="email"
                />
              </VCol>

              <!-- 👉 Organization -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="body.phone"
                  label="No. Telepon"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="body.roleId"
                  label="Select Role"
                  :items="roles"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
                
              >
                <VBtn type="submit">Save changes</VBtn>
                <VBtn color="error" @click="this.$router.back()">Back</VBtn>
                <VBtn
                  color="secondary"
                  variant="tonal"
                  type="reset"
                  @click.prevent="resetForm"
                >
                  Reset
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<script>
  import api from "@/apis/CommonAPI"
  import utils from "@/utils/CommonUtils"
  import { avatarText } from '@core/utils/formatters'
  import Swal from 'sweetalert2'

  export default {
    components: {
    },
    async mounted(){
      await this.doGetById(this.$route.params.id)
      await this.doSearchAllRole()
    },
    data(){
      return {
        roles: [],
        body: [],
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
      async doSearchAllRole(){
          this.loading = true
          let uri = `/api/v1/roles/all`;
          let responseBody = await api.jsonApi(uri,'GET');
          console.log("🚀 ~ doSearchAllRole ~ responseBody:", responseBody)
          if( responseBody.status != 200 ){
            this.errorMessage = responseBody.message;
          }else{
            this.roles = responseBody.data;
          }
          this.loading = false
      },
      async doUpdate(){
        Swal.fire({
          title: "Are you sure?",
          text: "You want to save this data?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#7367F0",
          cancelButtonColor: "#EA5455",
          confirmButtonText: "Yes",
          cancelButtonText: "Cancel",
          customClass: {
            confirmButton: 'confirm-button-text-white',
            cancelButton: 'confirm-button-text-white'
          }
      }).then(async (result) => {
        if (result.isConfirmed) {
          try{
            this.isFormValid = true
            let body = {
              userId: this.body.userId, 
              roleId: this.body.roleId, 
              nip: this.body.nip, 
              name: this.body.name,
              email: this.body.email,
              password: this.body.password || "",
              phone: this.body.phone,
              photo: this.body.photo_file || "",
              photo_name: this.body.photo_file != "" ? this.body.photo_name : "",
              photo_mime_type: this.body.photo_file != "" ? this.body.photo_mime_type : ""
            }
            console.log("🚀 ~ doAdd ~ body:", body)
    
            let uri = `/api/v1/users`;
            let responseBody = await api.jsonApi(uri, 'PUT', JSON.stringify(body));
            console.log("🚀 ~ doUpdate ~ responseBody:", responseBody)
            if( responseBody.status != 200 ){
              let msg = Array.isArray(responseBody.message) ? responseBody.message.toString() : responseBody.message;
              Swal.fire('Error!', msg, 'error')
            }else{
              await this.doGetById(this.$route.params.id)
              Swal.fire('Success!', responseBody.message, 'success')
            }
            this.loading = false
          }catch(error){
            Swal.fire('Error!', error, 'error')
          }
        }
      })
      },
      resetForm(){
        this.body = {...this.data}
        this.body.photo = ''
        this.body.password = ''
        this.body.roleId = this.data.role_id
      },
      async handleFileUpload(event) {
        const file = event.target.files[0];
        if(file.size > 1048576){
          return Swal.fire('Error!', 'Max file size 1 MB', 'error')
        }
        console.log("🚀 ~ handleFileUpload ~ file:", file)
        const base_64 = await utils.encodeFileToBase64(file)
        if (file && base_64) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.body.photo = e.target.result;
  
            let plain = base_64.split(",")
            this.body.photo_file = plain[1]
            this.body.photo_name = file.name
            this.body.photo_mime_type = file.type
            console.log({a: plain[1], b: file.name, c: file.type})
          };
          reader.readAsDataURL(file);
        }
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
            this.body = {...this.data}
            this.body.userId = this.$route.params.id
            this.body.roleId = responseBody.data.role_id
            this.body.photo = ''
            this.body.password = ''
            console.log("🚀 ~ doGetById ~ this.body:", this.body)
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

.confirm-button-text-white {
  color: white !important;
}
</style>

<route lang="yaml">
  meta:
    requiresLogin: true
</route>
