<template>
  <h2>Bonjour depuis mon composant!</h2>

  <p>{{ str }}</p>

  <p>Le nom de l'instance est:{{ name }}</p>
</template>

<script>
export default{
  data(){
    return{
      name:"Mycomposant",
      str:"Je suis le str de Mycomposant"
    }
  }
}
</script>

<style>
h2{
  color:red;
}
</style>