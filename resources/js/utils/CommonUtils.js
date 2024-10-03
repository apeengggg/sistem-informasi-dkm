
import Swal from 'sweetalert2'
import moment from 'moment'
import axios from 'axios'
import router from '../router'

const MSG_LIST = {'MSGCMN0001':'Error backend API \'{1}\', Error : \'{2}\''};

export default {
  // method
  formatRupiah(number){
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR", 
      minimumFractionDigits: (number > 99.9 && number % 1 === 0) ? 0 : 2,
    }).format(number)
  },
  formatDatetime(datetime){
    if(datetime)
      return moment(datetime).utcOffset('+0000').format('YYYY-MM-DD HH:mm')
    return '-'
  },
  formatDate(datetime){
    if(datetime)
      return moment(datetime).format('YYYY-MM-DD')
    return '-'
  },
  formatTime(datetime){
    if(datetime)
      return moment(datetime).format('HH:mm:ss')
    return '-'
  },
  getShiftFromValue(shift){
    return(shift =='1' || shift == 'R')
         ? 'Red'
         : (shift =='2' || shift == 'W')
            ? 'White'
            : (shift||'Non shift')
  },
  getHandlingFromValue(handling){
    switch(handling) {
      case 0:
        return 'OK'
      case 1:
        return 'Scrap'
      case 2:
        return 'Need Confirmation'
      case 3:
        return 'Repair'
      case 4:
        return 'Special Judge'
      case 5:
        return 'Already Repaired'
      default:
        return handling || ''
    }
  },
  getValueFromHandling(value){
    switch(value) {
      case 'OK':
        return 0
      case 'Scrap':
        return 1
      case 'Need Confirmation':
        return 2
      case 'Repair':
        return 3
      case 'Special Judge':
        return 4
      case 'Already Repaired':
        return 5
      default:
        return value || ''
    }
  },

  tooltipRangeDate(range_date){
    let dateRange = String(range_date).split(",")
    let dateFrom = dateRange[0] != 'null'?moment(dateRange[0]).format('MM/DD/YYYY'):''
    let dateTo = dateRange[1]?moment(dateRange[1]).format('MM/DD/YYYY'):''
    return dateFrom+' to '+dateTo
  },
  getTotal(arr){
      let sum = 0
      arr.map(e => sum += e)
      return sum
  },
  bubbleSort(arr,order) {
    var i, j;
    var len = arr.length;
    var isSwapped = false;
    for (i = 0; i < len; i++) {
        isSwapped = false;
        for (j = 0; j < (len - i - 1); j++) {
          if(order == 'asc')
            if (arr[j] > arr[j + 1]) {
                var temp = arr[j]
                arr[j] = arr[j + 1];
                arr[j + 1] = temp;
                isSwapped = true;
            }
          else if(order == 'desc'){
            if (arr[j] < arr[j + 1]) {
                var temp = arr[j]
                arr[j] = arr[j + 1];
                arr[j + 1] = temp;
                isSwapped = true;
            }
          }
        }
        if (!isSwapped) {
            break;
        }
    }
  },
  getImageRq(route, img_id, position_id, changed_date, local_storage_name){
    try {
      if(localStorage.getItem(local_storage_name)){
        // image storage exist
        let img_arr = JSON.parse(localStorage.getItem(local_storage_name))
        let found = false
        let img_obj
        img_arr.forEach(function(el, idx, arr) {
          // console.log('el', el)
          if(el.route === route && el.img_id === img_id && el.position_id === position_id &&  el.changed_date === changed_date){
            img_obj = {
              route: el.route,
              img_id: el.img_id,
              position_id: el.position_id,
              src:el.src,
              changed_date: el.changed_date
            }

            // console.log('img_obj from ls', img_obj)
            found = true
          }
        });
        if(found){
          return img_obj
        }else{
          return null
        }
      }
      return null
    }
    catch(err) {
      localStorage.removeItem(local_storage_name);
      return null
    }
  },
  setImageRq(route,img_id, position_id, src, changed_date, local_storage_name){
    changed_date = changed_date == undefined ? null : changed_date
    // console.log('setLocalImageRq', local_storage_name)
    try {
      let img_arr =  []
      let img_obj = {
        route: route,
        img_id: img_id,
        position_id: position_id,
        src:src,
        changed_date: changed_date
      }
      if(localStorage.getItem(local_storage_name)){
        // image storage exist
        img_arr = JSON.parse(localStorage.getItem(local_storage_name))
        let notFound = true
        img_arr.forEach(function(el, idx, arr) {
          if(el.route == img_obj.route && el.img_id == img_obj.img_id && el.position_id == img_obj.position_id && el.changed_date == img_obj.changed_dt){
            // update image src
            el.src = img_obj.src
            el.changed_date = img_obj.changed_date
            notFound = false
          }
        });
        if(notFound){
          // add image to storage
          img_arr.push(img_obj)
        }
      }else{
        // console.log('img_arr', img_arr, img_obj)
        img_arr.push(img_obj)
      }
      // console.log(local_storage_name, img_arr)
      localStorage.setItem(local_storage_name, JSON.stringify(img_arr))
    }
    catch(err) {
      console.log('errorSetImageRq', err)
      localStorage.removeItem(local_storage_name);
      return null
    }
  },
  getLocalImage(route,id){
    try {
      if(localStorage.getItem("img_storage")){
        // image storage exist
        let img_arr = JSON.parse(localStorage.getItem("img_storage"))
        let found = false
        let img_obj
        img_arr.forEach(function(el, idx, arr) {
          if(el.route == route && el.id == id){
            img_obj = {
              route: el.route,
              id: el.id,
              src:el.src,
              changedDate: el.changedDate
            }
            found = true
          }
        });
        if(found){
          return img_obj
        }else{
          return null
        }
      }
      return null
    }
    catch(err) {
      localStorage.removeItem("img_storage");
      return null
    }
  },
  setLocalImage(route,id,src,changedDate){
    try {
      let img_arr =  []
      let img_obj = {
        route: route,
        id: id,
        src:src,
        changedDate:changedDate
      }
      if(localStorage.getItem("img_storage")){
        // image storage exist
        img_arr = JSON.parse(localStorage.getItem("img_storage"))
        let notFound = true
        img_arr.forEach(function(el, idx, arr) {
          if(el.route == img_obj.route && el.id == img_obj.id){
            // update image src
            el.src = img_obj.src
            el.changedDate = img_obj.changedDate
            notFound = false
          }
        });
        if(notFound){
          // add image to storage
          img_arr.push(img_obj)
        }
      }else{
        // add image to storage
        img_arr.push(img_obj)
      }
      localStorage.setItem("img_storage", JSON.stringify(img_arr))
    }
    catch(err) {
      localStorage.removeItem("img_storage");
      return null
    }
  },
  clearLocalImage(){
    let name = []
    for (let i = 0; i < localStorage.length; i++) {
      if(localStorage.key(i).includes('img')){
        name.push(localStorage.key(i));
      }
    }
    for (let i = 0; i < name.length; i++) {
      localStorage.removeItem(name[i]);
    }
  },
  setSessionFilter(filter,sortBy,sortOrder){
    sessionStorage.setItem("filter", JSON.stringify(filter))
    sessionStorage.setItem("sortBy", sortBy)
    sessionStorage.setItem("sortOrder", sortOrder)
  },
  // message
  toast(message) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      type: 'success',
      title: 'Info:',
      text: message,
    })
  },
  error(message) {
    Swal.fire({
      heightAuto: false,
      icon: 'error',
      // eslint-disable-next-line no-useless-concat
      title: 'Oops...',
      text: message || 'Something went wrong',
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
    })
  },
  warning(message) {
    Swal.fire({
      heightAuto: false,
      icon: 'warning',
      // eslint-disable-next-line no-useless-concat
      title: 'Oops...',
      text: message || 'Something went wrong',
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
    })
  },
  closeSwal() {
    const elements = document.getElementsByClassName('.swal2-container')
    while (elements.length > 0) {
      elements[0].parentNode.removeChild(elements[0])
    }
  },
  getResponseMessage(value) {
    if (value.data) return value.data.message
    return value.message || 'Something went wrong'
  },
  backToPreviousPage() {
    Swal.fire({
      heightAuto: false,
      title: '<h5><strong>Leave this page?</strong></h5>',
      width: 300,
      showCancelButton: true,
      confirmButtonText: 'Ya',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary ml-1',
      },
    }).then(result => {
      if (result.value) {
        router.back()
      }
    })
  },
  async errorTokenSwalPassword(path, msg) {
    const noHp = localStorage.getItem('userNoHp')
    await Swal.fire({
      title: `<h5><strong> Token anda telah habis, silahkan masukkan kembali password untuk pengguna ${noHp}`,
      html: `Silahkan masukkan Kata Sandi untuk Nomor Telepon <strong>${noHp}</strong><br/>`,
      input: 'password',
      showDenyButton: true,
      confirmButtonText: 'Login',
      denyButtonText: 'Gunakan Nomor Lain',
      customClass: {
        confirmButton: 'btn btn-primary',
        denyButton: 'ml-1',
      },
      allowEscapeKey: false,
      showLoaderOnConfirm: true,
      showLoaderOnDeny: true,
      preConfirm: async password => {
        const form = {
          no_hp: localStorage.getItem('userNoHp'),
          password,
        }
        const res = await api.login(form)
        return res
      },
      allowOutsideClick: () => false,
      preDeny: () => {
        localStorage.clear()
        window.location.reload('/login')
      },
    }).then(res => {
      if (res.isConfirmed) {
        if (res.value.status === 200) {
          const result = res.value.data
          localStorage.setItem('userId', result.data.userId)
          localStorage.setItem('userEmail', result.data.userEmail)
          localStorage.setItem('userAvatar', result.data.userAvatar)
          localStorage.setItem('userAvatar64', result.data.userAvatar64 || '')
          localStorage.setItem('permission', JSON.stringify(result.data.userPermission) || '')
          localStorage.setItem('token', result.token)
          localStorage.setItem('userNoHp', result.data.userNoHp)
          axios.defaults.headers.common.Authorization = `Bearer ${result.token}`
          // axios.defaults.headers.common['auth-token'] = result.token
          // eslint-disable-next-line no-restricted-globals
          location.reload()
        } else {
          $toast.error(res.value.msg)
          this.errorToken(path, 'Password Salah!', false)
        }
      }
    }).catch(error => error)
  },
  async errorToken(path, msg) {
    await this.errorTokenSwalPassword(path, msg)
  },
  async message(msgId,msgParam){
    let msg = ''+MSG_LIST[msgId];
    let i = 0;
    for(i==0;i<msgParam.length;i++){
      msg = msg.replace('{'+(i+1)+'}',msgParam[i]);
    }
    return msg;
  },
  scrollOntop(){
    window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
  },
  SwalConfirmationPush(title, text, icon, confirmButtonText, cancelButtonText, s_callback, n_callback) {
    Swal.fire({
      title: title,
      text: text,
      showCancelButton: true,
      icon: icon,
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText,
      customClass: {
        confirmButton: 'btn btn-primary me-4',
        cancelButton: 'btn btn-outline-primary',
        buttonsStyling: false,
      },
    }).then(async (result) => {
      if (result.isConfirmed) {
        s_callback()
      }else{
        n_callback()
      }
    })
  },
  encodeFileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onloadend = () => {
            const base64String = reader.result;
            resolve(base64String);
        };

        reader.onerror = (error) => {
            reject(error);
        };

        reader.readAsDataURL(file); // This method reads the file as a data URL (Base64)
    });
}
}
