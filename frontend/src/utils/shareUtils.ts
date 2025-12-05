export interface ShareData {
  title: string;
  description: string;
  url: string;
  hashtags?: string[];
}

export const generateShareUrls = (data: ShareData) => {
  const { title, description, url, hashtags = [] } = data;
  const encodedTitle = encodeURIComponent(title);
  const encodedDescription = encodeURIComponent(description);
  const encodedUrl = encodeURIComponent(url);

  return {
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}&quote=${encodedTitle} - ${encodedDescription}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}&hashtags=${hashtags.join(',')}`,
    whatsapp: `https://wa.me/?text=${encodedTitle}%0A${encodedDescription}%0A${encodedUrl}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}&title=${encodedTitle}&summary=${encodedDescription}`,
    telegram: `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle} - ${encodedDescription}`,
  };
};

export const copyToClipboard = async (text: string): Promise<boolean> => {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return true;
    } else {
      // Fallback for older browsers
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.left = '-999999px';
      textarea.style.top = '-999999px';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      const success = document.execCommand('copy');
      textarea.remove();
      return success;
    }
  } catch (error) {
    console.error('Failed to copy to clipboard:', error);
    return false;
  }
};

export const canUseNativeShare = () => {
  return typeof navigator !== 'undefined' && 'share' in navigator;
};

export const nativeShare = async (data: ShareData): Promise<boolean> => {
  if (!canUseNativeShare()) {
    return false;
  }

  try {
    await navigator.share({
      title: data.title,
      text: data.description,
      url: data.url,
    });
    return true;
  } catch (error) {
    if ((error as Error).name !== 'AbortError') {
      console.error('Native share failed:', error);
    }
    return false;
  }
};

export const generateTicketShareData = (ticket: any): ShareData => {
  const baseUrl = window.location.origin;
  const ticketUrl = `${baseUrl}/tickets`; // You can make this more specific if you have individual ticket pages
  
  const title = `🎫 ${ticket.title || ticket.name || 'Amazing Ticket'}`;
  const price = ticket.adult_price || ticket.price || 'Special Price';
  const description = `Check out this amazing ticket! Starting at RM ${price}. Book now for an unforgettable experience!`;
  
  return {
    title,
    description,
    url: ticketUrl,
    hashtags: ['Kuantan188', 'Tickets', 'Events', 'Entertainment'],
  };
};