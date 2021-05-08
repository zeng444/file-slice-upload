<template>
  <div class="slice-wrap">
    <div class="file" v-if="value">
      <el-button icon="el-icon-tickets" class="info" type="text">{{value}}</el-button>
      <i class="el-icon-close" style="color:red" @click="remove"></i>
    </div>
    <div>
      <el-button @click="clickUp">
        {{btnText}}
        <i class="el-icon-upload2 el-icon--right"></i>
      </el-button>
      <input class="el-upload__input" type="file" ref="sliceUploadInput" @change="upload" />
      <span v-if="progress && showProgress">&nbsp;&nbsp;{{ (progress * 100).toFixed(2) }}%</span> <span class="error-info">{{msg}}</span>
    </div>
  </div>
</template>
<script>
  export default {
    name: 'sliceUpload',
    data () {
      return {
        msg: '',
        progress: ''
      }
    },
    props: {
      value: {
        type: String,
        default: ''
      },
      accept: {
        type: String,
        default: ''
      },
      showProgress: {
        type: Boolean,
        default: true
      },
      btnText: {
        type: String,
        default: '上传文件'
      },
      chunkSize: {
        type: String,
        default: '1'
      },
      api: {
        type: String,
        default: ''
      }
    },
    methods: {
      remove () {
        this.msg = ''
        this.progress = ''
        this.$refs.sliceUploadInput.value = ''
        this.$emit('input', '')
      },
      clickUp () {
        this.$refs.sliceUploadInput.click()
      },
      upload (e) {
        this.msg = ''
        this.progress = ''
        const file = e.target.files[0]
        if (this.accept && this.accept.indexOf(file.type) === -1) {
          this.msg = '不支持的文件格式' + file.type
          return this.$emit('on-error', this.msg)
        }
        const chunkSize = 1024 * 1024 * this.chunkSize
        const chunkQuntity = Math.ceil(file.size / chunkSize)
        this.sliceFile(chunkQuntity, 1, file, 0, chunkSize)
      },
      sliceFile (chunkQuntity, chunkNo, file, start, end) {
        const fileName = file.name
        const blob = file.slice(start, end)
        this.send(blob, fileName, chunkQuntity, chunkNo).then((res) => {
          res = res.data
          if (res.status !== 'success') {
            this.$refs.sliceUploadInput.value = ''
            this.progress = ''
            this.msg = res.message
            return this.$emit('on-error', this.msg)
          }
          this.progress = chunkNo / chunkQuntity
          this.$emit('on-progress', this.progress)
          if (res.data.isFinished !== true && chunkNo !== chunkQuntity) {
            start = end
            end = start + (1024 * 1024 * this.chunkSize)
            chunkNo++
            this.sliceFile(chunkQuntity, chunkNo, file, start, end)
          } else {
            this.msg = ''
            this.progress = ''
            this.$refs.sliceUploadInput.value = ''
            this.$emit('input', res.data.path)
            this.$emit('on-success', res.data.url, res.data.path)
          }
        }).catch((res) => {
          this.progress = ''
          this.$refs.sliceUploadInput.value = ''
          this.msg = '服务器错误'
          this.$emit('on-error', this.msg)
        })
      },
      send (blob, fileName, chunkQuntity, chunkNo) {
        return new Promise((resolve, reject) => {
          const formData = new FormData()
          formData.append('file', blob, fileName)
          formData.append('chunkNo', chunkNo)
          formData.append('chunkQuantity', chunkQuntity)
          this.$axios.post(this.api, formData, { headers: { 'Content-Type': 'multipart/form-data' }}).then((res) => {
            resolve(res)
          }).catch((res) => {
            reject(res)
          })
        })
      }
    }
  }
</script>
<style lang="scss" scoped>
  .slice-wrap{
    color:#999;
    font-size:14px;
    .file {
      display: flex;
      .info {
        color:#999;
        font-size:14px;
      }
      align-items: center;
      i{
        margin-left: 5px;
        cursor: pointer;
      }
    }
    .error-info{
      margin-left: 12px;
      font-size: 14px;
      color: red;
    }
  }
</style>
