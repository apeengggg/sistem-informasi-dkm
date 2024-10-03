<script setup>
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg
          class="auth-footer-mask"
          :src="authThemeMask"
        />
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <VNodeRenderer
            :nodes="themeConfig.app.logo"
            class="mb-6"
          />
          <h5 class="text-h5 font-weight-semibold mb-1">
            Welcome to {{ themeConfig.app.title }}! 👋🏻
          </h5>
          <p class="mb-0">
            Silahkan masuk menggunakan akun anda
          </p>
        </VCardText>
        <VCardText>
          <VForm @submit.prevent="() => login()">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <VCheckbox
                    v-model="form.remember"
                    label="Ingat saya"
                  />
                  <RouterLink
                    class="text-primary ms-2 mb-1"
                    :to="{ name: 'pages-authentication-forgot-password-v2' }"
                  >
                    Lupa Password?
                  </RouterLink>
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  Login
                </VBtn>
              </VCol>

              <!-- create account -->
              <VCol
                cols="12"
                class="text-center text-base"
              >
                <!-- <RouterLink
                  class="text-primary ms-2"
                  :to="{ name: 'pages-authentication-register-v2' }"
                >
                  Buat Akun
                </RouterLink> -->
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<script>
// import
import api from "@/apis/CommonAPI"
import Swal from 'sweetalert2'
import { useAppAbility } from '@/plugins/casl/useAppAbility'

export default {
  components: {

  },
  created(){
    this.ability = useAppAbility();
  },
  mounted(){
    localStorage.removeItem('token')
    localStorage.removeItem('user_data')
    localStorage.removeItem('userAbilities')
  },
  data(){
    return{
      ability: '',
      form: {
        email: '',
        password: '',
        remember: '',
      },
      isPasswordVisible: false,
      loading: false,
    }
  },
  methods: {
    async login(){
      this.loading = true

      let uri = `/api/v1/auth/login`;
      let responseBody = await api.jsonApi(uri,'POST', JSON.stringify(this.form));
      console.log('Message', Array.isArray(responseBody.message))
      if( responseBody.status != 200 ){
        let msg = ''
        if(Array.isArray(responseBody.message)){
          msg = responseBody.message.toString()
        }else{
          msg = responseBody.message
        }

        Swal.fire('Error!', msg, 'error')
      }else{
        localStorage.setItem('user_data', JSON.stringify(responseBody.data))
        localStorage.setItem('token', responseBody.data.token)
        this.ability.update([{action: 'manage',subject: 'all'}])
        this.$router.push('/apps/masters/users');
      }
      this.loading = false
    }
  },
  watch: {

  },
  computed: {

  }
}
</script>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
  meta:
    layout: blank
</route>