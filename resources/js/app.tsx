/*import { createInertiaApp } from '@inertiajs/svelte';
import { mount } from 'svelte';

createInertiaApp({
	resolve: (name) => {*/
		//const pages = import.meta.glob("./Pages/**/*.svelte", { eager: true });
		/*let page = pages[`./Pages/${name}.svelte`];
		return { default: page.default, layout: page.layout };
	},
	setup({ el, App, props }) {
		mount(App, { target: el, props });
	},
});
*/
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true })
    return pages[`./Pages/${name}.jsx`]
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
})
