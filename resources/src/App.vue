

<template>
    <button @click="addLike">like</button>
    <button @click="addDislike">dislike</button>
    <h1>кол-во лайков {{likes}}</h1>
    <h1>кол-во лайков {{dislikes}}</h1>
</template>

<script>
export default {
   data(){
       return{
           likes: 0,
           dislikes: 5,
       }
   },

   methods:{
       addLike(){
            this.likes +=1;
       },
       addDislike(){
           this.dislikes += 1;
       }
   }
}
</script>

<style scoped>

</style>
