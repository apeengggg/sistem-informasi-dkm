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
</script>
<template>
  <section>
    <VRow>
      <VCol
        v-for="meta in this.userListMeta"
        :key="meta.title"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard>
          <VCardText class="d-flex justify-space-between">
            <div>
              <span>{{ meta.title }}</span>
              <div class="d-flex align-center gap-2 my-1">
                <h6 class="text-h6">
                  {{ meta.stats }}
                </h6>
              </div>
              <span>{{ meta.subtitle }}</span>
            </div>

            <VAvatar
              rounded
              variant="tonal"
              :color="meta.color"
              :icon="meta.icon"
            />
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12">
        <VCard title="List Users">
          <!-- 👉 Filters -->
          <VCardText>
            <VRow>
              <VCol
                cols="12"
                sm="4"
              >
              <VTextField
                  v-model="param_query.name"
                  label="Name"
                  clearable
                  placeholder="Name"
                  density="compact"
                  v-on:keyup.enter="doSearch(1)"
                />
              </VCol>
              <VCol
                cols="12"
                sm="4"
              >
              <VTextField
                  v-model="param_query.email"
                  label="Email"
                  clearable
                  placeholder="Email"
                  density="compact"
                  v-on:keyup.enter="doSearch(1)" 
                />
              </VCol>
              <!-- 👉 Select Role -->
              <VCol
                cols="12"
                sm="4"
              >
                <VSelect
                  v-model="param_query.roleId"
                  label="Select Role"
                  :items="roles"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
              <!-- 👉 Select Status -->
              <VCol
                cols="12"
                sm="4"
              >
                <VSelect
                  v-model="selectedStatus"
                  label="Select Status"
                  :items="status"
                  clearable
                  clear-icon="tabler-x"
                />
              </VCol>
            </VRow>
          </VCardText>

          <VDivider />

          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div
              class="me-3"
              style="width: 80px;"
            >
              <VSelect
                v-model="this.page.pageSize"
                density="compact"
                variant="outlined"
                :items="[10, 20, 30, 50]"
              />
            </div>

            <VSpacer />

            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 10rem;">
              </div>

              <!-- 👉 Export button -->
              <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-screen-share"
              >
                Export
              </VBtn>

              <!-- 👉 Add user button -->
              <VBtn
                prepend-icon="tabler-plus"
                @click="openDrawer()"
              >
                Add New User
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  NAME
                </th>
                <th scope="col">
                  ROLE
                </th>
                <th scope="col">
                  STATUS
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr
                v-for="user in this.data"
                :key="user.user_id"
                style="height: 3.75rem;"
              >
                <!-- 👉 User -->
                <td>
                  <div class="d-flex align-center">
                    <VAvatar
                      variant="tonal"
                      :color="resolveUserRoleVariant(user.role_name).color"
                      class="me-3"
                      size="38"
                    >
                      <VImg
                        v-if="user.photo"
                        :src="user.photo"
                      />
                      <span v-else>{{ avatarText(user.name) }}</span>
                    </VAvatar>

                    <div class="d-flex flex-column">
                      <h6 class="text-base">
                        {{ user.name }}
                      </h6>
                      <span class="text-sm text-disabled">{{ user.email }}</span>
                    </div>
                  </div>
                </td>

                <!-- 👉 Role -->
                <td>
                  <VAvatar
                    :color="resolveUserRoleVariant(user.role_name).color"
                    :icon="resolveUserRoleVariant(user.role_name).icon"
                    variant="tonal"
                    size="30"
                    class="me-4"
                  />
                  <span class="text-capitalize text-base">{{ user.role_name }}</span>
                </td>

                <!-- 👉 Status -->
                <td>
                  <VChip
                    label
                    :color="resolveUserStatusVariant(user.status)"
                    size="small"
                    class="text-capitalize"
                  >
                    {{ user.status == 1 ? 'Active' : 'Inactive'}}
                  </VChip>
                </td>

                <!-- 👉 Actions -->
                <td
                  class="text-center"
                  style="width: 5rem;"
                >
                  <RouterLink
                    :to="{ name: 'apps-masters-users-id', params: { id: user.user_id } }"
                    class="font-weight-medium user-list-name"
                  >
                    <VBtn
                      icon
                      size="x-small"
                      color="default"
                      variant="text"
                    >
                      <VIcon
                        size="22"
                        icon="tabler-edit"
                      />
                    </VBtn>
                  </RouterLink>
                  <VBtn
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                    @click="doDelete(user.user_id)"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-trash"
                    />
                  </VBtn>

                  <!-- <VBtn
                    icon
                    size="x-small"
                    color="default"
                    variant="text"
                  >
                    <VIcon
                      size="22"
                      icon="tabler-dots-vertical"
                    />

                    <VMenu activator="parent">
                      <VList>
                        <VListItem
                          title="View"
                          :to="{ name: 'apps-user-view-id', params: { id: user.user_id } }"
                        />
                        <VListItem
                          title="Suspend"
                          href="javascript:void(0)"
                        />
                      </VList>
                    </VMenu>
                  </VBtn> -->
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->
            <tfoot v-show="!this.data.length">
              <tr>
                <td
                  colspan="7"
                  class="text-center"
                >
                  No data available
                </td>
              </tr>
            </tfoot>
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ paginationData }}
            </span>

            <VPagination
              v-model="this.page.pageNo"
              size="small"
              :total-visible="11"
              :length="this.page.totalPages"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VNavigationDrawer
      :width="400"
      location="end"
      class="scrollable-content"
      v-if="isAddNewUserDrawerVisible"
    >
      <div class="d-flex align-center pa-6 pb-1">
        <h6 class="text-h6">
          Add User
        </h6>
        <VSpacer />
        <VBtn
          variant="tonal"
          color="default"
          icon
          size="32"
          class="rounded"
          @click="this.isAddNewUserDrawerVisible = !this.isAddNewUserDrawerVisible"
        >
          <VIcon
            size="18"
            icon="tabler-x"
          />
        </VBTn>
      </div>
      <PerfectScrollbar :options="{ wheelPropagation: false }">
        <VCard flat>
          <VCardText>
            <VRow>
                <VCol cols="12">
                  <VTextField
                    v-model="body.name"
                    :rules="[requiredValidator]"
                    label="Nama"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="body.nip"
                    :rules="[requiredValidator]"
                    label="NIP"
                  />
                </VCol>
                <VCol cols="12">
                  <VSelect
                    v-model="body.roleId"
                    label="Pilih Role"
                    :rules="[requiredValidator]"
                    :items="roles"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="body.email"
                    :rules="[requiredValidator, emailValidator]"
                    label="Email"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="body.phone"
                    :rules="[requiredValidator]"
                    label="No Telepon"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                  v-model="body.password"
                  :append-inner-icon="isShowPassword ? 'tabler-eye' : 'tabler-eye-off'"
                  :rules="[requiredValidator, lengthValidator(specifiedLength, 4)]"
                  :type="isShowPassword ? 'text' : 'password'"
                  label="Password"
                  @click:append-inner="isShowPassword = !isShowPassword"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                  v-model="body.confirmation_password"
                  :append-inner-icon="isShowPassword ? 'tabler-eye' : 'tabler-eye-off'"
                  :rules="[requiredValidator, lengthValidator(specifiedLength, 4)]"
                  :type="isShowPassword ? 'text' : 'password'"
                  label="Confirmation Password"
                  @click:append-inner="isShowPassword = !isShowPassword"
                  />
                </VCol>
                <VCol cols="12">
                  <VFileInput
                    accept="image/jpeg, image/jpg, image/png"
                    label="Foto"
                    v-model="body.photo"
                    v-on:change="handleFileChange($event)"
                    :rules=[requiredValidator]
                  />
                </VCol>
                <VCol cols="12">
                  <VBtn
                    class="me-3"
                    @click="doAdd()"
                  >
                    Submit
                  </VBtn>
                  <VBtn
                    type="reset"
                    variant="tonal"
                    color="secondary"
                    @click="this.isAddNewUserDrawerVisible = !this.isAddNewUserDrawerVisible"
                  >
                    Cancel
                  </VBtn>
                </VCol>
              </VRow>
          </VCardText>
        </VCard>
      </PerfectScrollbar>
    </VNavigationDrawer>
  </section>
</template>

<script>
  import api from "@/apis/CommonAPI"
  import utils from "@/utils/CommonUtils"
  import AddNewUser from '@/views/apps/masters/user/AddNewUser.vue'
  import { useUserListStore } from '@/views/apps/user/useUserListStore'
  import { avatarText } from '@core/utils/formatters'
  import Swal from 'sweetalert2'
  

  export default {
    components: {
      AddNewUser
    },
    mounted(){
      this.doSearch(1)
      this.doSearchAllRole()
    },
    data(){
      return {
        isFormValid: true,
        isShowPassword: false,
        param_query: {
          name: '',
          email: '',
          roleId: ''
        },
        body: {
          roleId: '',
          name:'',
          email: '',
          phone: '',
          password: '',
          confirmation_password: '',
          photo: '',
          photo_file: '',
          photo_name: '',
          photo_mime_type: '',
        },
        selectedStatus: 1,
        loading: false,
        data: [],
        infoMessage: '',
        warningMessage: '',
        errorMessage: '',
        page: {
          totalRecords: 0,
          totalPages: 0,
          pageNo: 1,
          pageSize: 10
        },
        orderBy: 'user_id',
        dir: 'asc',
        userListMeta: [
        {
            icon: 'tabler-user',
            color: 'primary',
            title: 'Total Users',
            stats: 0,
          },
          {
            icon: 'tabler-user-check',
            color: 'success',
            title: 'Active Users',
            stats: 0,
          },
          {
            icon: 'tabler-user-x',
            color: 'danger',
            title: 'Inactive Users',
            stats: 0,
          },
        ],
        isAddNewUserDrawerVisible: false,
        status: [
          {
            title: 'Active',
            value: 1,
          },
          {
            title: 'Inactive',
            value: 0,
          },
        ],
        roles: []
      }
    },
    watch: {
      'page.pageNo'() {
          if (this.page.pageNo > this.page.totalPages){
              this.page.pageNo = this.page.totalPages
          }
          this.doSearch(this.page.pageNo)
        },
      'page.pageSize'(){
        this.doSearch(this.page.pageNo)
      },
      'selectedStatus'(){
        this.doSearch(this.page.pageNo)
      },
      'param_query.roleId'(){
        this.doSearch(this.page.pageNo)
      }
    },
    computed: {
      paginationData(){
        const firstIndex = this.data.length ? (this.page.pageNo - 1) * this.page.pageSize + 1 : 0
        const lastIndex = this.data.length + (this.page.pageNo - 1) * this.page.pageSize
  
        return `Showing ${ firstIndex } to ${ lastIndex } of ${ this.data.length } entries`
      }
    },
    methods: {
      openDrawer(){
        this.isAddNewUserDrawerVisible = true
        console.log('opened', this.isAddNewUserDrawerVisible)
      },
      async handleFileChange(e){
        const file = e.target.files[0]

        try{
          const base_64 = await utils.encodeFileToBase64(file)
          if(base_64){
            let plain = base_64.split(",")
            this.body.photo_file = plain[1]
            this.body.photo_name = file.name
            this.body.photo_mime_type = file.type
          }
        }catch(error){
          this.body.photo = ''
          this.body.photo_file = ''
          this.body.photo_name = ''
          this.body.photo_mime_type = ''
          Swal.fire('Error!', error, 'error')
        }
      },
      async doAdd(){
        try{
          this.isFormValid = true
          let body = {
            roleId: this.body.roleId, 
            nip: this.body.nip, 
            name: this.body.name,
            email: this.body.email,
            password: this.body.password,
            phone: this.body.phone,
            photo: this.body.photo_file,
            photo_name: this.body.photo_name,
            photo_mime_type: this.body.photo_mime_type
          }
          console.log("🚀 ~ doAdd ~ body:", body)
  
          if(this.body.password != this.body.confirmation_password){
            this.isFormValid = false
            return Swal.fire('Error!', 'Password Tidak Sama Dengan Confirmation Password', 'error')
          }
          let uri = `/api/v1/users`;
          let responseBody = await api.jsonApi(uri, 'POST', JSON.stringify(body));
          if( responseBody.status != 200 ){
            let msg = Array.isArray(responseBody.message) ? responseBody.message.toString() : responseBody.message;
            Swal.fire('Error!', msg, 'error')
          }else{
            Swal.fire('Success!', responseBody.message, 'success')
          }
          this.loading = false
        }catch(error){
          Swal.fire('Error!', error, 'error')
        }
      },
      async doSearch(page){
          this.loading = true
          let param = `orderBy=${this.orderBy}&dir=${this.dir}&perPage=${this.page.pageSize}&page=${page}&status=${this.selectedStatus == null ? 1 : this.selectedStatus}`

          if(this.param_query.name != "" && this.param_query.name != "null"){
            param += `&name=${this.param_query.name}`
          }

          if(this.param_query.email != "" && this.param_query.email != "null"){
            param += `&email=${this.param_query.email}`
          }

          if(this.param_query.roleId != "" && this.param_query.roleId != "null"){
            param += `&roleId=${this.param_query.roleId}`
          }

          let uri = `/api/v1/users?${param}`;
          let responseBody = await api.jsonApi(uri,'GET');
          // console.log("🚀 ~ doSearch ~ responseBody:", responseBody)
          if( responseBody.status != 200 ){
            this.infoMessage = '';
            this.warningMessage = '';
            this.errorMessage = responseBody.message;
          }else{
            await this.resolveMetaUsers(responseBody.meta_users)
            this.data = responseBody.data.data;
            // console.log("🚀 ~ doSearch ~ this.data:", this.data)

            this.page.totalRecords= responseBody.data.total
            this.page.totalPages= responseBody.data.last_page
            this.page.pageNo= responseBody.data.current_page
            this.page.pageSize= responseBody.data.per_page
            if(responseBody.data.data.length<=0){
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
      async doSearchAllRole(page){
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
      resolveMetaUsers(data){
        this.userListMeta.forEach(obj => {
          if(obj.title == 'Total Users'){
            obj.stats = data.total_users
          }

          if(obj.title == 'Active Users'){
            obj.stats = data.active_users
          }

          if(obj.title == 'Inactive Users'){
            obj.stats = data.inactive_users
          }
        })
      },
      resolveUserRoleVariant(role){
        if (role.toLowerCase() === 'subscriber')
          return {
            color: 'warning',
            icon: 'tabler-user',
          }
        if (role.toLowerCase() === 'author')
          return {
            color: 'success',
            icon: 'tabler-circle-check',
          }
        if (role.toLowerCase() === 'maintainer')
          return {
            color: 'primary',
            icon: 'tabler-chart-pie-2',
          }
        if (role.toLowerCase() === 'editor')
          return {
            color: 'info',
            icon: 'tabler-pencil',
          }
        if (role.toLowerCase() === 'admin')
          return {
            color: 'secondary',
            icon: 'tabler-device-laptop',
          }
        
        return {
          color: 'primary',
          icon: 'tabler-user',
        }
      },
      resolveUserStatusVariant(stat){
        if(stat == 1){
          return 'success'
        }
        
        return 'danger'
      }
    },
  }
</script>Role

<style lang="scss">
.app-user-search-filter {
  inline-size: 31.6rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity));
}

.confirm-button-text-white {
    color: white !important;
}
</style>

<route lang="yaml">
  meta:
    requiresLogin: true
</route>
