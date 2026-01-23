import React from 'react';
import Link from 'next/link';
import Image from 'next/image';
import ManageDetailsJS, { AppendUTMToAnchor, CalendlyEmbed, SmoothScrollJS, TabImage, TableToggle, TextAnimation, ViewMoreToggle } from './Custom';
import fs from 'fs';
import path from 'path';
import DataDropForm from '../Form/DataDropForm/FormHTML';
import { TransformRelatedTemplates } from '../TransformRelatedTemplates';
import Tabbing from '../Tabbing';
import PlatformTabSection from '../PlatformTabSection';
import WorksFlowSwiper from '@/app/(defaultLayout)/byroles/WorksFlowSwiper';
import { parseWorkFlowSwiperShortcode } from '@/app/(defaultLayout)/byroles/parseWorkFlowShortcode';

type TOCItem = {
  id: string;
  title: string;
  children?: TOCItem[];
};

export const tocItems: TOCItem[] = [];
let currentH2: TOCItem | null = null;

// Generate alt text from image filename when no alt text is available
function getAltFromFilename(url: string): string {
  try {
    const pathname = new URL(url).pathname;
    const filename = pathname.split('/').pop() || '';
    // Remove extension and replace hyphens/underscores with spaces
    const nameWithoutExt = filename.replace(/\.[^/.]+$/, '');
    return nameWithoutExt.replace(/[-_]/g, ' ').replace(/\s+/g, ' ').trim();
  } catch {
    const filename = url.split('/').pop() || '';
    const nameWithoutExt = filename.replace(/\.[^/.]+$/, '');
    return nameWithoutExt.replace(/[-_]/g, ' ').replace(/\s+/g, ' ').trim();
  }
}

export const createTransformFunction = (altTextMap?: Record<string, string>) => {
  let buttonCounter = 0;
  let utmAnchorAdded = false;
  let firstLargeImageFound = false;

  return function replaceAnchorAndImageTags(node: any) {
    if (node.type === 'details') {
      return (
        <>
          <ManageDetailsJS/>
          <details className={node.props.className} open={node.props.open}>
            {React.Children.map(node.props.children, (child) => {
              if (!child || typeof child === 'string') return null;
              
              if (child.type === 'summary') {
                return <summary><h3>{child.props.children}</h3></summary>;
              }
              
              if (child.type === 'p') {
                return <p>{child.props.children}</p>;
              }
              
              if (child.type === 'figure') {
                return <figure>{child.props.children}</figure>;
              }
              
              return null;
            })}
          </details>
        </>
      );
    }
    if (node.type === 'div' && node.props.className?.includes('common-faq-wrapper')) {
      // Function to find all details nodes recursively
      const findAllDetails = (nodes: any[]): any[] => {
        if (!Array.isArray(nodes)) return [];
        
        return nodes.flatMap(child => {
          if (!child || !child.props) return [];
          
          // If this is a details node, return it
          if (child.type === 'details') return [child];
          
          // If this is a fragment or other component with children, search its children
          if (child.props.children) {
            const children = Array.isArray(child.props.children) 
              ? child.props.children 
              : [child.props.children];
            return findAllDetails(children);
          }
          
          return [];
        });
      };
    
      const children = Array.isArray(node.props.children) 
        ? node.props.children 
        : [node.props.children];
      
      // Find all details nodes in the tree
      const allDetails = findAllDetails(children);
    
      if (allDetails.length === 0) {
        return node;
      }
    
      // Process each details node and create schema for each
      const schemaScripts = allDetails.map((detailsNode, index) => {
        const questionNode = detailsNode.props.children?.find(
          (child: any) => child?.type === 'summary'
        );
        const answerNode = detailsNode.props.children?.find(
          (child: any) => child?.type === 'p' || child?.type === 'div'
        );
    
        if (!questionNode || !answerNode) return null;
    
        const questionText = extractTextFromNode(questionNode.props.children);
        const answerText = extractTextFromNode(answerNode.props.children).replace(/\s+/g, ' ').trim();
    
        return {
          '@type': 'Question',
          'name': questionText,
          'acceptedAnswer': {
            '@type': 'Answer',
            'text': answerText
          }
        };
      }).filter(Boolean); // Remove any null entries
    
      if (schemaScripts.length === 0) {
        return node;
      }
    
      // Create a single FAQPage schema with all questions
      const faqSchema = {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        'mainEntity': schemaScripts
      };
    
      return (
        <>
          <ManageDetailsJS/>
          <script
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(faqSchema) }}
          />
          {node}
        </>
      );
    }
    if (node.type === 'a' && node.props.href) {
      const linkHref = node.props.href;
      if (linkHref.includes('app.fieldcamp.ai')) {
        
        const utmButtonValue = `btn-${extractTextFromNode(node.props.children)}-${++buttonCounter}`;
        const anchorElement = (
          <a href={linkHref} title={extractTextFromNode(node.props.children)} className={`utm-medium-signup ${node.props.className}`} rel={node.props.rel} target={node.props.target} data-medium={utmButtonValue} style={node.props.style}>
            {node.props.children}
          </a>
        );

        if (!utmAnchorAdded) {
          utmAnchorAdded = true;
          return (
            <>
              <AppendUTMToAnchor/>
              {anchorElement}
            </>
          );
        }
        
        return anchorElement;
      } else if (linkHref.includes('calendly.com')) {
        return (
          <>
            <CalendlyEmbed/>
            <Link href={linkHref} title={extractTextFromNode(node.props.children)} className={`calendly-open ${node.props.className}`} rel={node.props.rel} target={node.props.target} style={node.props.style}>
              {node.props.children}
            </Link>
          </>
        );
      } else if (linkHref.startsWith('#')) {
        return (
          <>
            <Link href={linkHref} title={extractTextFromNode(node.props.children)} className={node.props.className} rel={node.props.rel} target={node.props.target} style={node.props.style}>
              {node.props.children}
            </Link>
            <SmoothScrollJS />
          </>
        );
      } else {
        return (
          <Link href={linkHref} title={extractTextFromNode(node.props.children)} className={node.props.className} rel={node.props.rel} target={node.props.target} style={node.props.style}>
            {node.props.children}
          </Link>
        );
      }
    } 
    
    // Handle standalone <img> tags (not inside figure)
    if (node.type === 'img' && node.props?.src) {
      const imageUrl = node.props.src;
      const isSVGImage = imageUrl?.endsWith('.svg');
      const imageAlt = altTextMap?.[imageUrl] || node.props.alt || getAltFromFilename(imageUrl);

      if (isSVGImage) {
        return (
          <img
            key={imageUrl}
            src={imageUrl}
            alt={imageAlt}
            width={node.props.width}
            height={node.props.height}
            style={node.props.style}
            className={node.props.className || ''}
            loading="lazy"
          />
        );
      }

      const imageWidth = (node.props?.style?.width && !node.props?.style?.width.endsWith('%') && node.props?.style?.width.slice(0, -2)) || node.props.width || 2000;
      const imageHeight = node.props.height || Math.round(imageWidth * 0.5625);
      const widthStyle = imageWidth == 2000 ? '100%' : `${imageWidth}px`;
      const imageStyle = { width: widthStyle, height: 'auto' };
      const hasSkipLazyClass = node.props.className?.includes('skip-lazy');

      // First large image (likely LCP) should be priority loaded
      const isLargeImage = imageWidth >= 1000 || imageHeight >= 500;
      const shouldPrioritize = !firstLargeImageFound && isLargeImage;
      if (shouldPrioritize) {
        firstLargeImageFound = true;
      }

      return (
        <Image
          key={imageUrl}
          src={imageUrl}
          alt={imageAlt}
          width={imageWidth}
          height={imageHeight}
          style={imageStyle}
          sizes={node.props.srcset || '100vw'}
          className={node.props.className || ''}
          priority={node.props.priority || hasSkipLazyClass || shouldPrioritize}
          loading={(hasSkipLazyClass || shouldPrioritize) ? 'eager' : 'lazy'}
        />
      );
    }

    // Handle images inside <figure> tags
    if (node.type === 'figure' && node.props.children?.type === 'img') {
      const imageUrl = node.props.children.props.src;
      const isSVGImage = imageUrl?.endsWith('.svg');
      const imageAlt = altTextMap?.[imageUrl] || node.props.children.props.alt || getAltFromFilename(imageUrl);

      if (isSVGImage) {
        // For SVG images, return with updated alt text
        return (
          <figure key={imageUrl} className={node.props.className || ''}>
            <img
              src={imageUrl}
              alt={imageAlt}
              width={node.props.children.props.width}
              height={node.props.children.props.height}
              style={node.props.children.props.style}
              className={node.props.children.props.className || ''}
              loading="lazy"
            />
          </figure>
        );
      }

      const imageSizes = node.props.children.props.srcset || '100vw';
      const imageWidth = (node.props.children.props?.style?.width && !node.props.children.props?.style?.width.endsWith('%') && node.props.children.props?.style?.width.slice(0, -2)) || node.props.children.props.width || 2000;
      const imageHeight = node.props.height || Math.round(imageWidth * 0.5625); // Default to 16:9 aspect ratio if height not specified
      const widthStyle = imageWidth == 2000 ? '100%' : `${imageWidth}px`;
      const imageStyle = { width: widthStyle, height: 'auto' };

      const parentFigure = node.parent?.props;
      const hasSkipLazyClass =
        node.props.className?.includes('skip-lazy') ||
        parentFigure?.className?.includes('skip-lazy');

      // First large image (likely LCP) should be priority loaded
      const isLargeImage = imageWidth >= 1000 || imageHeight >= 500;
      const shouldPrioritize = !firstLargeImageFound && isLargeImage;
      if (shouldPrioritize) {
        firstLargeImageFound = true;
      }

      return (
        <figure key={imageUrl} className={node.props.className || ''}>
          <Image
            key={imageUrl}
            src={imageUrl}
            alt={imageAlt}
            width={imageWidth}
            height={imageHeight}
            style={imageStyle}
            sizes={imageSizes}
            className={node.props.children.props.className || ''}
            priority={node.props.children.props.priority || hasSkipLazyClass || shouldPrioritize}
            loading={(hasSkipLazyClass || shouldPrioritize) ? 'eager' : 'lazy'}
          />
        </figure>
      );
    }

    if (node.type === 'div' && node.props?.className?.includes('animation-text')) {
      return (
        <TextAnimation key={node.props.className}>
          {node.props.children}
        </TextAnimation>
      );
    }

    if (node.type === 'div' && node.props?.className?.includes('image-faq-ul')) {
      return (
        <div key={node.props.className} className={node.props.className}>
            {node.props.children}
            <TabImage />
          </div>
      );
    }

    if (node.type === "h2" && !node.props?.className?.includes("exclude-toc")) {
      
      const toc_id = generateAnchorId(node.props.children);
      currentH2 = {
        id: toc_id,
        title: extractTextFromNode(node.props.children),
        children: [],
      };
      tocItems.push(currentH2);
      return (
        <h2 key={toc_id} id={toc_id} className={`${node.props.className} toc_heading`}>
          {node.props.children}
        </h2>
      );
    }

    if (node.type === "h3" && !node.props?.className?.includes("exclude-toc")) {
      const toc_id = generateAnchorId(node.props.children);
      // currentH3 = {
      //   id: toc_id,
      //   title: extractTextFromNode(node.props.children),
      //   children: [],
      // };
      // tocItems.push(currentH3);
      return (
        <h3 key={toc_id} id={toc_id} className={`${node.props.className} toc_heading`}>
          {node.props.children}
        </h3>
      );
    }
    if (node.props?.children === '[dataDropForm]') {
      return (
        <div className="drop-form-container">
          <DataDropForm />
        </div>
      );
    }

    // Hide calculator shortcodes (they are rendered separately in page.tsx)
    const shortcodes = [
      '[invoiceGenerator]',
      '[receiptGenerator]',
      '[houseCleaningCalculator]',
      '[lawnCareCalculator]',
      '[roofingCalculator]',
      '[profitForecastCalculator]',
      '[hvacCFMCalculator]',
      '[pipeVolumeCalculator]',
      '[hvacLoadCalculator]',
      '[laborCostCalculator]',
      '[achCalculator]',
      '[estimateGenerator]',
      '[hvacDuctCalculator]'
    ];
    const childText = typeof node.props?.children === 'string' ? node.props.children.trim() : '';
    if (childText && shortcodes.includes(childText)) {
      return null;
    }

    // Render WorksFlowSwiper inline where shortcode appears
    if (childText && childText.startsWith('[workFlowSwiper') && childText.endsWith(']')) {
      const swiperData = parseWorkFlowSwiperShortcode(childText);
      return (
        <WorksFlowSwiper
          title={swiperData?.title}
          subtitle={swiperData?.subtitle}
          buttonText={swiperData?.buttonText}
          buttonLink={swiperData?.buttonLink}
          roles={swiperData?.roles}
        />
      );
    }

    // Also handle paragraph tags that contain workFlowSwiper shortcode
    if (node.type === 'p' && typeof node.props?.children === 'string') {
      const pText = node.props.children.trim();
      if (shortcodes.includes(pText)) {
        return null;
      }
      // Render WorksFlowSwiper inline for paragraph containing shortcode
      if (pText.startsWith('[workFlowSwiper') && pText.endsWith(']')) {
        const swiperData = parseWorkFlowSwiperShortcode(pText);
        return (
          <WorksFlowSwiper
            title={swiperData?.title}
            subtitle={swiperData?.subtitle}
            buttonText={swiperData?.buttonText}
            buttonLink={swiperData?.buttonLink}
            roles={swiperData?.roles}
          />
        );
      }
    }

    if (node.type === 'table' && node.props.className?.includes('hide-show-table')) {
      return (
        <>
          <table className={node.props.className}>{node.props.children}</table>
          <TableToggle />
        </>
      );
    }

    if (node.type === 'div' && node.props.className?.includes('view-toggle')) {
      const children = node.props.children.map((child: any, index: number) => {
        if (child.type === 'div' && child.props.className?.includes('toggle-content')) {
          return React.cloneElement(child, {key: `toggle-content-${index}`, className: 'toggle-content hidden' });
        }
        if (child.type === 'p' && child.props.className?.includes('view-less-text')) {
          return React.cloneElement(child, {key: `view-less-text-${index}`, className: 'view-less-text hidden' });
        }
        return child;
      });
      
      return (
        <>
          <div className={node.props.className}>
            {children}
          </div>
        <ViewMoreToggle />
        </>
      );
    }

    if (node.type === 'div' && node.props.className?.includes('related-templates')) {
      return (
        <div className={node.props.className}>
          <TransformRelatedTemplates html={node.props.children} />
        </div>  
      );
    }

    if(node.type === 'div' && node.props.className?.includes('pricing-tab-button')) {
      return (

        <div>
          <Tabbing/>
          {node.props.children}
          </div>
      );
    }

    if(node.type === 'div' && node.props.className?.includes('platform-tab-button')) {
      return (
        <div className={node.props.className}>
          <PlatformTabSection/>
          {node.props.children}
        </div>
      );
    }

    return node;
  };
}

function extractTextFromNode(node: any): string {
  if (typeof node === "string") {
    return node;
  }
  if (Array.isArray(node)) {
    return node.map(extractTextFromNode).join(" ");
  }
  if (node?.props?.children) {
    return extractTextFromNode(node.props.children);
  }
  return "";
}

function generateAnchorId(node: any) {
  const text = extractTextFromNode(node);
  if (typeof text !== "string") {
    return ""; // or handle this case as needed
  }
  // Remove HTML tags and special characters
  const cleanText = text.replace(/<[^>]*>?/gm, "").replace(/[^\w\s]/gi, "");
  return cleanText.replace(/\s+/g, "-").toLowerCase();
}
