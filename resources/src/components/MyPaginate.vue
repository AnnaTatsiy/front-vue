<script setup>
const props = defineProps({
    currPage: Number,
    totalPage: Number
});
</script>

<template>
    <div class="d-flex justify-content-end">
        <nav aria-label="navigation">
            <ul class="pagination">
                <li class="page-item">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <div @click="$emit('change', page)" :key="page" v-for="page in totalPage">
                    <li class="page-item" :class="{
                      'active': page === currPage
                  }"> <a class="page-link" href="#">{{page}}</a></li>
                </div>
                <li class="page-item">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<style scoped>

</style>
