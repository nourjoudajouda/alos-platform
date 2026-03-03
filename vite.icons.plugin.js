import fs from 'fs/promises';
import path from 'path';
import { getIconsCSS } from '@iconify/utils';

export default function iconifyPlugin() {
  return {
    name: 'vite-iconify-plugin',
    apply: 'build',

    async buildStart() {
      console.log('Generating iconify CSS file...');
      try {
        const iconSetPaths = [path.resolve(process.cwd(), 'node_modules/@iconify/json/json/tabler.json')];
        const iconSets = await Promise.all(
          iconSetPaths.map(async (filePath) => {
            const data = await fs.readFile(filePath, 'utf-8');
            return JSON.parse(data);
          })
        );
        const allIcons = iconSets
          .map((iconSet) =>
            getIconsCSS(iconSet, Object.keys(iconSet.icons), {
              iconSelector: '.{prefix}-{name}',
              commonSelector: '.ti',
              format: 'expanded'
            })
          )
          .join('\n');
        const outputPath = path.resolve(process.cwd(), 'resources/assets/vendor/fonts/iconify/iconify.css');
        const dir = path.dirname(outputPath);
        await fs.mkdir(dir, { recursive: true });
        await fs.writeFile(outputPath, allIcons, 'utf8');
        const additionalFiles = [
          { name: 'fontawesome', filesPath: path.resolve(process.cwd(), 'node_modules/@fortawesome/fontawesome-free/webfonts'), destPath: path.resolve(process.cwd(), 'resources/assets/vendor/fonts/fontawesome') },
          { name: 'flags', filesPath: path.resolve(process.cwd(), 'node_modules/flag-icons/flags'), destPath: path.resolve(process.cwd(), 'resources/assets/vendor/fonts/flags') }
        ];
        for (const file of additionalFiles) {
          await fs.mkdir(file.destPath, { recursive: true });
          const items = await fs.readdir(file.filesPath, { withFileTypes: true });
          for (const item of items) {
            const srcPath = path.join(file.filesPath, item.name);
            const destPath = path.join(file.destPath, item.name);
            if (item.isDirectory()) {
              await fs.mkdir(destPath, { recursive: true });
              const subItems = await fs.readdir(srcPath);
              for (const subItem of subItems) {
                await fs.copyFile(path.join(srcPath, subItem), path.join(destPath, subItem));
              }
            } else {
              await fs.copyFile(srcPath, destPath);
            }
          }
        }
        console.log('Iconify CSS generated.');
      } catch (error) {
        console.error('Error generating Iconify CSS:', error);
      }
    }
  };
}
