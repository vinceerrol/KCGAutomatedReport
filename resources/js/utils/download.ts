import { toast } from 'vue-sonner';

/**
 * Downloads a CSV file with guaranteed filename, .csv extension, and user toast feedback.
 * Works seamlessly across Chrome, Edge, Safari, Firefox on Windows/Mac.
 */
export async function downloadCsv(url: string, suggestedFilename: string): Promise<void> {
    const filename = suggestedFilename.endsWith('.csv') ? suggestedFilename : `${suggestedFilename}.csv`;
    const toastId = toast.loading(`Preparing export: ${filename}...`);

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'text/csv',
            },
        });

        if (!response.ok) {
            throw new Error(`Server returned status ${response.status}: ${response.statusText}`);
        }

        const blob = await response.blob();
        
        // Ensure explicit text/csv MIME type with utf-8 charset
        const csvBlob = new Blob([blob], { type: 'text/csv;charset=utf-8;' });
        const blobUrl = window.URL.createObjectURL(csvBlob);

        const anchor = document.createElement('a');
        anchor.href = blobUrl;
        anchor.download = filename;
        anchor.style.display = 'none';
        document.body.appendChild(anchor);
        anchor.click();

        // Cleanup
        setTimeout(() => {
            document.body.removeChild(anchor);
            window.URL.revokeObjectURL(blobUrl);
        }, 150);

        toast.success(`Export downloaded: ${filename}`, { id: toastId });
    } catch (error: any) {
        console.error('CSV download error:', error);
        toast.error(`Failed to download CSV: ${error?.message || 'Network error'}`, { id: toastId });
        
        // Direct link fallback if fetch fails
        const fallbackAnchor = document.createElement('a');
        fallbackAnchor.href = url;
        fallbackAnchor.download = filename;
        fallbackAnchor.click();
    }
}
