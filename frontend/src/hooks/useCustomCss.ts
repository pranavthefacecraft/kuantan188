import { useEffect } from 'react';
import { API_BASE_URL } from '../config/api';

/**
 * Hook that fetches active custom CSS from the admin backend
 * and injects it into the page as a <style> tag.
 */
export function useCustomCss() {
  useEffect(() => {
    const styleId = 'admin-custom-css';

    fetch(`${API_BASE_URL}/public/custom-css`)
      .then(res => {
        if (!res.ok) throw new Error('Failed to load custom CSS');
        return res.text();
      })
      .then(css => {
        if (!css.trim()) return;
        let style = document.getElementById(styleId) as HTMLStyleElement | null;
        if (!style) {
          style = document.createElement('style');
          style.id = styleId;
          document.head.appendChild(style);
        }
        style.textContent = css;
      })
      .catch(() => {
        // Silently ignore — custom CSS is non-critical
      });

    return () => {
      const style = document.getElementById(styleId);
      if (style) style.remove();
    };
  }, []);
}
