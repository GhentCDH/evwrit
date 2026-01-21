declare module '*.vue' {
  import Vue from 'vue'
  export default Vue
}

declare module '*.pug' {
  const content: any
  export default content
}

