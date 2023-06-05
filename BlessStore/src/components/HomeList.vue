<template>
    <div>
        <a-list :grid="{ gutter: 24, xs: 1, sm: 2, md: 4, lg: 4, xl: 6, xxl: 6, xxxl: 12 }" :data-source="data">
            <template #renderItem="{ item }">
                <a-list-item>
                    <a-card :title="item.title">Card content</a-card>
                </a-list-item>
            </template>
        </a-list>
    </div>
</template>
<script>
import { defineComponent } from 'vue';
const data = [{
    title: 'Title 1',
}, {
    title: 'Title 2',
}, {
    title: 'Title 3',
}, {
    title: 'Title 4',
}, {
    title: 'Title 5',
}, {
    title: 'Title 6',
}];
export default defineComponent({
    setup() {
        return {
            data,
        };
    },
});
</script>