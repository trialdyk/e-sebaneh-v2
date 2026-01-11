<template>
    <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all duration-200">
        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
            <UButton 
                v-for="item in toolbarItems" 
                :key="item.icon"
                :icon="item.icon"
                :color="editor.isActive(item.name, item.attributes) ? 'primary' : 'neutral'"
                variant="ghost"
                size="xs"
                :disabled="!editor.can().chain().focus()[item.action](item.attributes).run()"
                @click="item.action ? editor.chain().focus()[item.action](item.attributes).run() : null"
                :title="item.label"
            />
            
            <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></div>

            <UButton
                icon="i-lucide-undo"
                color="neutral"
                variant="ghost"
                size="xs"
                :disabled="!editor.can().chain().focus().undo().run()"
                @click="editor.chain().focus().undo().run()"
                title="Undo"
            />
            <UButton
                icon="i-lucide-redo"
                color="neutral"
                variant="ghost"
                size="xs"
                :disabled="!editor.can().chain().focus().redo().run()"
                @click="editor.chain().focus().redo().run()"
                title="Redo"
            />
        </div>

        <!-- Editor Content -->
        <editor-content :editor="editor" class="prose prose-sm dark:prose-invert max-w-none p-4 min-h-[300px] outline-none" />
    </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import { watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Link.configure({
            openOnClick: false,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'min-h-[300px] outline-none',
        },
    },
    onUpdate: () => {
        emit('update:modelValue', editor.value.getHTML())
    },
})

watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value
    if (isSame) {
        return
    }
    editor.value.commands.setContent(value, false)
})

const toolbarItems = [
    { icon: 'i-lucide-bold', action: 'toggleBold', name: 'bold', label: 'Bold' },
    { icon: 'i-lucide-italic', action: 'toggleItalic', name: 'italic', label: 'Italic' },
    { icon: 'i-lucide-strikethrough', action: 'toggleStrike', name: 'strike', label: 'Strike' },
    { icon: 'i-lucide-code', action: 'toggleCode', name: 'code', label: 'Code' },
    { icon: 'i-lucide-heading-1', action: 'toggleHeading', name: 'heading', attributes: { level: 1 }, label: 'H1' },
    { icon: 'i-lucide-heading-2', action: 'toggleHeading', name: 'heading', attributes: { level: 2 }, label: 'H2' },
    { icon: 'i-lucide-heading-3', action: 'toggleHeading', name: 'heading', attributes: { level: 3 }, label: 'H3' },
    { icon: 'i-lucide-list', action: 'toggleBulletList', name: 'bulletList', label: 'Bullet List' },
    { icon: 'i-lucide-list-ordered', action: 'toggleOrderedList', name: 'orderedList', label: 'Ordered List' },
    { icon: 'i-lucide-quote', action: 'toggleBlockquote', name: 'blockquote', label: 'Blockquote' },
]

onBeforeUnmount(() => {
    editor.value.destroy()
})
</script>

<style scoped>
:deep(.prose) {
  max-width: none;
}
:deep(.prose ul) {
  list-style-type: disc;
  padding-left: 1.5em;
}
:deep(.prose ol) {
  list-style-type: decimal;
  padding-left: 1.5em;
}
:deep(.prose h1) {
  font-size: 2em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
:deep(.prose h2) {
  font-size: 1.5em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
:deep(.prose h3) {
  font-size: 1.17em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
:deep(.prose blockquote) {
    border-left: 4px solid #e5e7eb;
    padding-left: 1em;
    font-style: italic;
}
:deep(.dark .prose blockquote) {
    border-left-color: #374151;
}
:deep(.prose code) {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25em;
}
:deep(.dark .prose code) {
    background-color: #1f2937;
    color: #e5e7eb;
}
</style>
