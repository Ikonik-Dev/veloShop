import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    root: ".",
    plugins: [tailwindcss()],
    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
    build: {
        outDir: "public/build",
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: "assets/main.js",
            output: {
                entryFileNames: "assets/main.js",
                chunkFileNames: "assets/[name].js",
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith(".css")) {
                        return "assets/main.css";
                    }

                    return "assets/[name][extname]";
                },
            },
        },
    },
});
